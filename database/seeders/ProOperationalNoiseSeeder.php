<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReceipt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds real-world operational noise to the PRO load-test dataset.
 *
 * V2 fix:
 * - finds load-test orders even when order_number / notes were not used by ProOrderLoadTestSeeder
 * - falls back to the 10 generated customer emails
 * - falls back to HH_LOAD_TEST_START_DATE + HH_LOAD_TEST_DAYS
 */
class ProOperationalNoiseSeeder extends Seeder
{
    private string $prefix;

    public function run(): void
    {
        $this->prefix = (string) env('HH_LOAD_TEST_PREFIX', 'LT');

        if ($seed = env('HH_NOISE_SEED')) {
            mt_srand((int) $seed);
        }

        DB::transaction(function (): void {
            $orders = $this->loadTestOrders();

            if ($orders->isEmpty()) {
                $this->command?->warn('No load-test orders found.');
                $this->command?->warn('Checked: order_number prefix, notes prefix, meta JSON, generated customer emails, and configured date range.');
                return;
            }

            $this->command?->info('Operational noise target orders: ' . $orders->count());

            $this->applyPaymentStatuses($orders);
            $this->applyCancelledOrders($orders);
            $this->applyPickupSlotPressure($orders);
            $this->createInventoryAdjustments($orders);
            $this->applySupplierDelaysAndPartialReceipts();

            $this->command?->info('Operational noise generated successfully.');
        });
    }

    private function loadTestOrders(): Collection
    {
        $query = Order::query();

        $columns = $this->columnsFor((new Order())->getTable());

        $query->where(function ($q) use ($columns): void {
            if (in_array('order_number', $columns, true)) {
                $q->orWhere('order_number', 'like', $this->prefix . '-ORD-%');
            }

            if (in_array('notes', $columns, true)) {
                $q->orWhere('notes', 'like', '%' . $this->prefix . '%')
                    ->orWhere('notes', 'like', '%load test%');
            }

            if (in_array('meta', $columns, true)) {
                $q->orWhere('meta', 'like', '%' . $this->prefix . '%')
                    ->orWhere('meta', 'like', '%load_test%')
                    ->orWhere('meta', 'like', '%load test%');
            }

            $customerIds = $this->loadTestCustomerIds();

            if ($customerIds->isNotEmpty()) {
                if (in_array('user_id', $columns, true)) {
                    $q->orWhereIn('user_id', $customerIds);
                }

                if (in_array('customer_id', $columns, true)) {
                    $q->orWhereIn('customer_id', $customerIds);
                }
            }

            $this->orWhereConfiguredDateRange($q, $columns);
        });

        return $query->orderBy('created_at')->get();
    }

    private function loadTestCustomerIds(): Collection
    {
        if (! class_exists(User::class)) {
            return collect();
        }

        return User::query()
            ->where(function ($query): void {
                $query
                    ->where('email', 'like', 'customer%@example.com')
                    ->orWhere('email', 'like', strtolower($this->prefix) . '.customer.%@example.test')
                    ->orWhere('email', 'like', strtolower($this->prefix) . '-customer-%@example.test')
                    ->orWhere('email', 'like', '%' . strtolower($this->prefix) . '%load%test%');
            })
            ->pluck('id');
    }

    private function orWhereConfiguredDateRange($query, array $columns): void
    {
        if (! in_array('created_at', $columns, true)) {
            return;
        }

        $days = (int) env('HH_LOAD_TEST_DAYS', 30);
        $start = env('HH_LOAD_TEST_START_DATE');

        if (! $start) {
            return;
        }

        $from = Carbon::parse($start)->startOfDay();
        $to = $from->copy()->addDays($days)->endOfDay();

        $query->orWhereBetween('created_at', [$from, $to]);
    }

    private function applyPaymentStatuses(Collection $orders): void
    {
        $pendingRate = (float) env('HH_NOISE_PAYMENT_PENDING_RATE', 0.10);
        $failedRate = (float) env('HH_NOISE_PAYMENT_FAILED_RATE', 0.03);
        $refundRate = (float) env('HH_NOISE_REFUND_RATE', 0.02);

        foreach ($orders as $order) {
            $roll = $this->randomFloat();

            $paymentStatus = 'paid';

            if ($roll < $failedRate) {
                $paymentStatus = 'failed';
            } elseif ($roll < $failedRate + $pendingRate) {
                $paymentStatus = 'pending';
            } elseif ($roll < $failedRate + $pendingRate + $refundRate) {
                $paymentStatus = 'refunded';
            }

            $this->safeUpdate($order, [
                'payment_status' => $paymentStatus,
                'payment_method' => $this->weightedPaymentMethod(),
                'payment_reference' => $this->prefix . '-PAY-' . strtoupper(Str::random(10)),
                'payment_noise_generated' => true,
            ]);
        }
    }

    private function applyCancelledOrders(Collection $orders): void
    {
        $cancelRate = (float) env('HH_NOISE_CANCEL_RATE', 0.08);

        foreach ($orders as $order) {
            if ($this->randomFloat() > $cancelRate) {
                continue;
            }

            $cancelledAt = Carbon::parse($order->created_at)->addHours(mt_rand(1, 24));

            $this->safeUpdate($order, [
                'status' => 'cancelled',
                'cancelled_at' => $cancelledAt,
                'cancellation_reason' => $this->randomCancellationReason(),
                'payment_status' => $this->randomFloat() < 0.4 ? 'refunded' : 'cancelled',
            ]);

            $this->createCancellationStockReturn($order, $cancelledAt);
        }
    }

    private function createCancellationStockReturn(Order $order, Carbon $date): void
    {
        if (! class_exists(InventoryMovement::class)) {
            return;
        }

        $items = method_exists($order, 'items') ? $order->items()->get() : collect();

        foreach ($items as $item) {
            $meta = $this->decodeMeta($item->meta ?? null);
            $ingredients = $meta['ingredient_snapshot'] ?? $meta['ingredients'] ?? [];

            if (! is_array($ingredients)) {
                continue;
            }

            foreach ($ingredients as $ingredient) {
                $ingredientId = $ingredient['ingredient_id'] ?? $ingredient['id'] ?? null;
                $quantity = (float) ($ingredient['quantity'] ?? $ingredient['required_quantity'] ?? 0);

                if (! $ingredientId || $quantity <= 0) {
                    continue;
                }

                $this->createInventoryMovement([
                    'ingredient_id' => $ingredientId,
                    'quantity' => round($quantity * (float) ($item->quantity ?? 1), 4),
                    'movement_type' => 'cancel_return',
                    'direction' => 'in',
                    'reason' => 'Cancelled order material release',
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'occurred_at' => $date,
                    'created_at' => $date,
                    'updated_at' => $date,
                    'notes' => $this->prefix . ' operational noise: cancellation return',
                ]);
            }
        }
    }

    private function applyPickupSlotPressure(Collection $orders): void
    {
        $slots = [
            ['08:00', '09:00', 35],
            ['09:00', '10:00', 30],
            ['10:00', '11:00', 20],
            ['11:00', '12:00', 10],
            ['15:00', '16:00', 5],
        ];

        foreach ($orders as $order) {
            [$from, $to] = $this->weightedSlot($slots);
            $date = Carbon::parse($order->created_at);

            $this->safeUpdate($order, [
                'pickup_date' => $date->toDateString(),
                'pickup_time_from' => $from . ':00',
                'pickup_time_to' => $to . ':00',
                'pickup_slot_label' => $from . '-' . $to,
            ]);
        }
    }

    private function createInventoryAdjustments(Collection $orders): void
    {
        if (! class_exists(InventoryMovement::class)) {
            return;
        }

        $days = (int) env('HH_NOISE_STOCK_ADJUSTMENT_DAYS', 30);
        $firstOrderDate = Carbon::parse($orders->min('created_at'))->startOfDay();

        $ingredientIds = InventoryMovement::query()
            ->whereNotNull('ingredient_id')
            ->distinct()
            ->pluck('ingredient_id');

        if ($ingredientIds->isEmpty()) {
            return;
        }

        for ($day = 0; $day < $days; $day++) {
            if ($this->randomFloat() > 0.35) {
                continue;
            }

            $date = $firstOrderDate->copy()->addDays($day)->setTime(mt_rand(7, 18), mt_rand(0, 59));
            $direction = $this->randomFloat() < 0.65 ? 'out' : 'in';

            $this->createInventoryMovement([
                'ingredient_id' => $ingredientIds->random(),
                'quantity' => round(mt_rand(1, 250) / 10, 3),
                'movement_type' => $direction === 'out' ? 'adjustment_out' : 'adjustment_in',
                'direction' => $direction,
                'reason' => $this->randomAdjustmentReason($direction),
                'occurred_at' => $date,
                'created_at' => $date,
                'updated_at' => $date,
                'notes' => $this->prefix . ' operational noise: stock adjustment',
            ]);
        }
    }

    private function applySupplierDelaysAndPartialReceipts(): void
    {
        if (! class_exists(Purchase::class)) {
            return;
        }

        $delayRate = (float) env('HH_NOISE_SUPPLIER_DELAY_RATE', 0.20);
        $partialRate = (float) env('HH_NOISE_PARTIAL_RECEIPT_RATE', 0.15);

        $columns = $this->columnsFor((new Purchase())->getTable());

        $purchases = Purchase::query()
            ->where(function ($query) use ($columns): void {
                if (in_array('purchase_number', $columns, true)) {
                    $query->orWhere('purchase_number', 'like', $this->prefix . '-PUR-%');
                }

                if (in_array('notes', $columns, true)) {
                    $query->orWhere('notes', 'like', '%' . $this->prefix . '%')
                        ->orWhere('notes', 'like', '%load test%');
                }

                if (in_array('created_at', $columns, true) && env('HH_LOAD_TEST_START_DATE')) {
                    $days = (int) env('HH_LOAD_TEST_DAYS', 30);
                    $from = Carbon::parse(env('HH_LOAD_TEST_START_DATE'))->startOfDay();
                    $to = $from->copy()->addDays($days)->endOfDay();
                    $query->orWhereBetween('created_at', [$from, $to]);
                }
            })
            ->get();

        foreach ($purchases as $purchase) {
            $isDelayed = $this->randomFloat() < $delayRate;
            $isPartial = $this->randomFloat() < $partialRate;

            $payload = [];

            if ($isDelayed) {
                $expectedDate = $this->dateValue($purchase, ['expected_date', 'ordered_at', 'created_at']) ?? now();

                $payload['expected_date'] = Carbon::parse($expectedDate)->toDateString();
                $payload['received_at'] = Carbon::parse($expectedDate)->addDays(mt_rand(1, 5));
                $payload['delivery_status'] = 'delayed';
            }

            if ($isPartial) {
                $payload['status'] = 'partially_received';
                $payload['delivery_status'] = $isDelayed ? 'delayed_partial' : 'partial';
                $payload['notes'] = trim(($purchase->notes ?? '') . PHP_EOL . $this->prefix . ' operational noise: partial supplier fulfillment');
            }

            $this->safeUpdate($purchase, $payload);

            if ($isPartial) {
                $this->applyPartialReceiptToItems($purchase);
            }
        }
    }

    private function applyPartialReceiptToItems(Purchase $purchase): void
    {
        if (! class_exists(PurchaseItem::class)) {
            return;
        }

        $items = method_exists($purchase, 'items')
            ? $purchase->items()->get()
            : PurchaseItem::query()->where('purchase_id', $purchase->id)->get();

        foreach ($items as $item) {
            $orderedQuantity = (float) ($item->quantity ?? $item->ordered_quantity ?? 0);

            if ($orderedQuantity <= 0) {
                continue;
            }

            $receivedQuantity = round($orderedQuantity * (mt_rand(55, 90) / 100), 4);

            $this->safeUpdate($item, [
                'received_quantity' => $receivedQuantity,
                'remaining_quantity' => max(0, round($orderedQuantity - $receivedQuantity, 4)),
                'status' => 'partially_received',
            ]);

            if (class_exists(PurchaseReceipt::class)) {
                $this->safeCreate(PurchaseReceipt::class, [
                    'purchase_id' => $purchase->id,
                    'receipt_number' => sprintf('%s-RC-NOISE-%05d-%05d', $this->prefix, $purchase->id, $item->id),
                    'received_date' => Carbon::parse($this->dateValue($purchase, ['received_at', 'created_at']) ?? now())->toDateString(),
                    'status' => 'draft',
                    'total_received_value' => 0,
                    'purchase_item_id' => $item->id,
                    'ingredient_id' => $item->ingredient_id ?? null,
                    'quantity' => $receivedQuantity,
                    'received_quantity' => $receivedQuantity,
                    'received_at' => $this->dateValue($purchase, ['received_at', 'created_at']) ?? now(),
                    'notes' => $this->prefix . ' operational noise: partial receipt',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function createInventoryMovement(array $payload): void
    {
        $this->safeCreate(InventoryMovement::class, $payload);
    }

    private function safeCreate(string $modelClass, array $payload): void
    {
        if (! class_exists($modelClass)) {
            return;
        }

        $model = new $modelClass();
        $columns = $this->columnsFor($model->getTable());

        $filtered = array_filter(
            $payload,
            static fn ($value, string $key): bool => in_array($key, $columns, true) && $value !== null,
            ARRAY_FILTER_USE_BOTH
        );

        if ($filtered !== []) {
            $modelClass::query()->create($filtered);
        }
    }

    private function safeUpdate(object $model, array $payload): void
    {
        if ($payload === []) {
            return;
        }

        $columns = $this->columnsFor($model->getTable());

        $filtered = array_filter(
            $payload,
            static fn ($value, string $key): bool => in_array($key, $columns, true) && $value !== null,
            ARRAY_FILTER_USE_BOTH
        );

        if ($filtered !== []) {
            $model->forceFill($filtered)->save();
        }
    }

    private function columnsFor(string $table): array
    {
        static $cache = [];

        if (! isset($cache[$table])) {
            $cache[$table] = DB::getSchemaBuilder()->getColumnListing($table);
        }

        return $cache[$table];
    }

    private function weightedPaymentMethod(): string
    {
        return $this->weighted([
            'card' => 65,
            'cash' => 25,
            'bank_transfer' => 10,
        ]);
    }

    private function randomCancellationReason(): string
    {
        return collect([
            'Customer cancelled before production',
            'Customer changed pickup date',
            'Duplicate order',
            'Payment failed',
            'Admin test cancellation',
        ])->random();
    }

    private function randomAdjustmentReason(string $direction): string
    {
        if ($direction === 'in') {
            return collect([
                'Inventory count surplus',
                'Found stock during recount',
                'Supplier rounding correction',
            ])->random();
        }

        return collect([
            'Inventory count shortage',
            'Damaged package',
            'Measurement correction',
            'Expired small batch',
        ])->random();
    }

    private function weightedSlot(array $slots): array
    {
        $sum = array_sum(array_column($slots, 2));
        $roll = mt_rand(1, $sum);
        $cursor = 0;

        foreach ($slots as $slot) {
            $cursor += $slot[2];

            if ($roll <= $cursor) {
                return [$slot[0], $slot[1]];
            }
        }

        return [$slots[0][0], $slots[0][1]];
    }

    private function weighted(array $weights): string
    {
        $sum = array_sum($weights);
        $roll = mt_rand(1, $sum);
        $cursor = 0;

        foreach ($weights as $value => $weight) {
            $cursor += $weight;

            if ($roll <= $cursor) {
                return (string) $value;
            }
        }

        return (string) array_key_first($weights);
    }

    private function randomFloat(): float
    {
        return mt_rand(0, 10000) / 10000;
    }

    private function decodeMeta(mixed $meta): array
    {
        if (is_array($meta)) {
            return $meta;
        }

        if (is_string($meta) && $meta !== '') {
            $decoded = json_decode($meta, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function dateValue(object $model, array $fields): mixed
    {
        foreach ($fields as $field) {
            if (isset($model->{$field}) && $model->{$field}) {
                return $model->{$field};
            }
        }

        return null;
    }
}
