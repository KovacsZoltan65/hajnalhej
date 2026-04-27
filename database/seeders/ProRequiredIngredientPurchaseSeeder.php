<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientSupplierTerm;
use App\Models\InventoryMovement;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProRequiredIngredientPurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@hajnalhej.hu')->first()
            ?? User::query()->first();

        $startDate = Carbon::parse(env('HH_LOAD_TEST_START_DATE', Carbon::today()->subDays(((int) env('HH_LOAD_TEST_DAYS', 30)) - 1)->toDateString()))->startOfDay();
        $days = max(1, (int) env('HH_LOAD_TEST_DAYS', 30));
        $endDate = $startDate->copy()->addDays($days - 1)->endOfDay();
        $safetyMultiplier = max(1.0, (float) env('HH_LOAD_TEST_PURCHASE_SAFETY_MULTIPLIER', 1.15));

        $fallbackSupplier = $this->fallbackSupplier();
        $demandByIngredient = $this->collectDemand($startDate, $endDate);

        if ($demandByIngredient === []) {
            return;
        }

        DB::transaction(function () use ($demandByIngredient, $fallbackSupplier, $admin, $startDate, $safetyMultiplier): void {
            $grouped = [];

            foreach ($demandByIngredient as $ingredientId => $quantity) {
                $ingredient = Ingredient::query()->find($ingredientId);
                if (! $ingredient) {
                    continue;
                }

                $term = $this->preferredTerm($ingredient);
                $supplier = $term?->supplier ?? $fallbackSupplier;
                $unitCost = (float) ($term?->unit_cost_override
                    ?? $term?->last_unit_cost
                    ?? $term?->average_unit_cost
                    ?? $ingredient->average_unit_cost
                    ?? $ingredient->estimated_unit_cost
                    ?? 1000);

                $recommendedQuantity = $this->roundPurchaseQuantity(
                    rawQuantity: (float) $quantity * $safetyMultiplier,
                    minimumOrderQuantity: (float) ($term?->minimum_order_quantity ?? 0),
                    packSize: (float) ($term?->pack_size ?? 0),
                );

                $supplierId = $supplier->id;
                $grouped[$supplierId]['supplier'] = $supplier;
                $grouped[$supplierId]['lead_time_days'] = max((int) ($term?->lead_time_days ?? $supplier->lead_time_days ?? 0), 0);
                $grouped[$supplierId]['items'][] = [
                    'ingredient' => $ingredient,
                    'quantity' => $recommendedQuantity,
                    'unit_cost' => $unitCost,
                    'demand_quantity' => (float) $quantity,
                    'term_id' => $term?->id,
                ];
            }

            foreach ($grouped as $supplierId => $group) {
                /** @var Supplier $supplier */
                $supplier = $group['supplier'];
                $leadTimeDays = (int) $group['lead_time_days'];
                $purchaseDate = $startDate->copy()->subDays(max($leadTimeDays, 1));
                $receivedDate = $startDate->copy()->subDay();
                $referenceNumber = sprintf('LT-PO-%s-%05d', $startDate->format('Ymd'), $supplierId);

                $purchase = Purchase::query()->updateOrCreate(
                    ['reference_number' => $referenceNumber],
                    [
                        'supplier_id' => $supplier->id,
                        'purchase_date' => $purchaseDate->toDateString(),
                        'expected_delivery_date' => $receivedDate->toDateString(),
                        'received_date' => $receivedDate->toDateString(),
                        'status' => Purchase::STATUS_POSTED,
                        'receipt_status' => 'received',
                        'subtotal' => 0,
                        'total' => 0,
                        'received_total' => 0,
                        'notes' => 'PRO load test automatikus alapanyag-beszerzés',
                        'created_by' => $admin?->id,
                        'ordered_at' => $purchaseDate->copy()->setTime(8, 0),
                        'posted_at' => $purchaseDate->copy()->setTime(8, 5),
                    ],
                );

                $purchase->items()->delete();

                $purchaseTotal = 0.0;
                $createdPurchaseItems = [];

                foreach ($group['items'] as $item) {
                    /** @var Ingredient $ingredient */
                    $ingredient = $item['ingredient'];
                    $quantity = round((float) $item['quantity'], 3);
                    $unitCost = round((float) $item['unit_cost'], 4);
                    $lineTotal = round($quantity * $unitCost, 2);
                    $purchaseTotal += $lineTotal;

                    $purchaseItem = PurchaseItem::query()->create([
                        'purchase_id' => $purchase->id,
                        'ingredient_id' => $ingredient->id,
                        'quantity' => $quantity,
                        'unit' => $ingredient->unit,
                        'unit_cost' => $unitCost,
                        'line_total' => $lineTotal,
                    ]);

                    $createdPurchaseItems[] = [
                        'purchase_item' => $purchaseItem,
                        'ingredient' => $ingredient,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'line_total' => $lineTotal,
                    ];
                }

                $purchase->update([
                    'subtotal' => round($purchaseTotal, 2),
                    'total' => round($purchaseTotal, 2),
                    'received_total' => round($purchaseTotal, 2),
                ]);

                $receipt = PurchaseReceipt::query()->updateOrCreate(
                    ['receipt_number' => sprintf('LT-RC-%s-%05d', $startDate->format('Ymd'), $supplierId)],
                    [
                        'purchase_id' => $purchase->id,
                        'received_date' => $receivedDate->toDateString(),
                        'status' => 'posted',
                        'total_received_value' => round($purchaseTotal, 2),
                        'notes' => 'PRO load test automatikus bevételezés',
                        'received_by' => $admin?->id,
                        'posted_at' => $receivedDate->copy()->setTime(7, 30),
                    ],
                );

                $receipt->items()->delete();

                foreach ($createdPurchaseItems as $item) {
                    /** @var PurchaseItem $purchaseItem */
                    $purchaseItem = $item['purchase_item'];
                    /** @var Ingredient $ingredient */
                    $ingredient = $item['ingredient'];

                    PurchaseReceiptItem::query()->create([
                        'purchase_receipt_id' => $receipt->id,
                        'purchase_item_id' => $purchaseItem->id,
                        'ingredient_id' => $ingredient->id,
                        'ordered_quantity' => $item['quantity'],
                        'received_quantity' => $item['quantity'],
                        'rejected_quantity' => 0,
                        'unit' => $ingredient->unit,
                        'unit_cost' => $item['unit_cost'],
                        'line_total' => $item['line_total'],
                        'quality_status' => 'accepted',
                        'notes' => 'PRO load test teljes bevételezés',
                    ]);

                    InventoryMovement::query()->create([
                        'ingredient_id' => $ingredient->id,
                        'movement_type' => InventoryMovement::TYPE_PURCHASE_IN,
                        'direction' => InventoryMovement::DIRECTION_IN,
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['line_total'],
                        'occurred_at' => $receivedDate->copy()->setTime(7, 30),
                        'reference_type' => PurchaseReceipt::class,
                        'reference_id' => $receipt->id,
                        'notes' => 'PRO load test alapanyag-beszerzés bevételezés',
                        'created_by' => $admin?->id,
                    ]);

                    Ingredient::query()
                        ->whereKey($ingredient->id)
                        ->increment('current_stock', $item['quantity']);

                    $freshStock = (float) Ingredient::query()->whereKey($ingredient->id)->value('current_stock');
                    Ingredient::query()
                        ->whereKey($ingredient->id)
                        ->update([
                            'average_unit_cost' => $item['unit_cost'],
                            'stock_value' => round($freshStock * (float) $item['unit_cost'], 2),
                        ]);
                }
            }
        });
    }

    /**
     * @return array<int, float>
     */
    private function collectDemand(Carbon $startDate, Carbon $endDate): array
    {
        $demand = [];

        OrderItem::query()
            ->whereHas('order', function ($query) use ($startDate, $endDate): void {
                $query->where('order_number', 'like', 'LT-%')
                    ->whereBetween('placed_at', [$startDate, $endDate]);
            })
            ->chunkById(250, function ($items) use (&$demand): void {
                foreach ($items as $item) {
                    $recipeSnapshot = $item->recipe_snapshot ?? [];
                    foreach ($recipeSnapshot as $recipeLine) {
                        $ingredientId = (int) ($recipeLine['ingredient_id'] ?? 0);
                        if ($ingredientId <= 0) {
                            continue;
                        }

                        $quantity = round((float) ($recipeLine['quantity_per_unit'] ?? 0) * (int) $item->quantity, 3);
                        $demand[$ingredientId] = ($demand[$ingredientId] ?? 0) + $quantity;
                    }
                }
            });

        return $demand;
    }

    private function preferredTerm(Ingredient $ingredient): ?IngredientSupplierTerm
    {
        return IngredientSupplierTerm::query()
            ->with('supplier')
            ->where('ingredient_id', $ingredient->id)
            ->where('active', true)
            ->orderByDesc('preferred')
            ->orderByRaw('unit_cost_override is null')
            ->orderBy('unit_cost_override')
            ->orderBy('id')
            ->first();
    }

    private function fallbackSupplier(): Supplier
    {
        return Supplier::query()->updateOrCreate(
            ['name' => 'Hajnalhéj Load Test Nagyker'],
            [
                'email' => 'loadtest-supplier@hajnalhej.test',
                'phone' => '+36 1 900 0000',
                'tax_number' => '99999999-2-42',
                'lead_time_days' => 2,
                'minimum_order_value' => 10000,
                'active' => true,
                'currency' => 'HUF',
                'notes' => 'Automatikus tesztbeszállító nagy mennyiségű load test adathoz.',
                'meta' => ['load_test' => true],
            ],
        );
    }

    private function roundPurchaseQuantity(float $rawQuantity, float $minimumOrderQuantity, float $packSize): float
    {
        $quantity = max($rawQuantity, $minimumOrderQuantity, 0.001);

        if ($packSize > 0) {
            $quantity = ceil($quantity / $packSize) * $packSize;
        }

        return round($quantity, 3);
    }
}
