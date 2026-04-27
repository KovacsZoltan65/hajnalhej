<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReceipt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProOrderLoadTestSeeder extends Seeder
{
    public function run(): void
    {
        $reset = filter_var(env('HH_LOAD_TEST_RESET', true), FILTER_VALIDATE_BOOLEAN);

        if ($reset) {
            $this->resetLoadTestData();
        }

        mt_srand((int) env('HH_LOAD_TEST_SEED', 20260427));

        $days = max(1, (int) env('HH_LOAD_TEST_DAYS', 30));
        $startDate = Carbon::parse(env('HH_LOAD_TEST_START_DATE', Carbon::today()->subDays($days - 1)->toDateString()))->startOfDay();

        /** @var EloquentCollection<int, User> $customers */
        $customers = User::query()
            ->where('email', 'like', 'loadtest.customer%@hajnalhej.test')
            ->orderBy('id')
            ->get();

        /** @var EloquentCollection<int, Product> $products */
        $products = Product::query()
            ->with(['productIngredients.ingredient'])
            ->where('is_active', true)
            ->whereHas('productIngredients')
            ->orderBy('id')
            ->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($days, $startDate, $customers, $products): void {
            for ($dayOffset = 0; $dayOffset < $days; $dayOffset++) {
                $date = $startDate->copy()->addDays($dayOffset);
                $dailyCustomerCount = mt_rand(2, min(7, $customers->count()));
                $dailyCustomers = $customers->shuffle()->take($dailyCustomerCount)->values();

                foreach ($dailyCustomers as $customerIndex => $customer) {
                    $targetMin = 5000;
                    $targetMax = 8000;
                    $cart = $this->buildCart($products, $targetMin, $targetMax);

                    if ($cart->isEmpty()) {
                        continue;
                    }

                    $subtotal = (float) $cart->sum('line_total');
                    $placedAt = $date->copy()->setTime(mt_rand(7, 18), mt_rand(0, 59));
                    $pickupDate = $date->copy()->addDay();

                    $order = Order::query()->create([
                        'order_number' => sprintf('LT-%s-%02d-%02d', $date->format('Ymd'), $customerIndex + 1, mt_rand(10, 99)),
                        'user_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'customer_email' => $customer->email,
                        'customer_phone' => sprintf('+36 30 900 %04d', $customer->id % 10000),
                        'status' => Order::STATUS_COMPLETED,
                        'currency' => 'HUF',
                        'subtotal' => $subtotal,
                        'total' => $subtotal,
                        'material_cost_total' => 0,
                        'notes' => 'PRO load test rendelés',
                        'pickup_date' => $pickupDate->toDateString(),
                        'pickup_time_slot' => $this->randomPickupSlot(),
                        'placed_at' => $placedAt,
                        'confirmed_at' => $placedAt->copy()->addMinutes(mt_rand(3, 25)),
                        'completed_at' => $pickupDate->copy()->setTime(mt_rand(8, 15), mt_rand(0, 59)),
                        'metadata' => [
                            'load_test' => true,
                            'generator' => static::class,
                            'target_range' => [$targetMin, $targetMax],
                        ],
                    ]);

                    $materialCostTotal = 0.0;
                    $ingredientDemand = [];

                    foreach ($cart as $cartLine) {
                        /** @var Product $product */
                        $product = $cartLine['product'];
                        $quantity = (int) $cartLine['quantity'];
                        $recipeSnapshot = $this->buildRecipeSnapshot($product);

                        OrderItem::query()->create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_name_snapshot' => $product->name,
                            'unit_price' => $cartLine['unit_price'],
                            'quantity' => $quantity,
                            'line_total' => $cartLine['line_total'],
                            'recipe_snapshot' => $recipeSnapshot,
                            'metadata' => ['load_test' => true],
                        ]);

                        foreach ($recipeSnapshot as $recipeLine) {
                            $ingredientId = (int) $recipeLine['ingredient_id'];
                            $neededQuantity = round((float) $recipeLine['quantity_per_unit'] * $quantity, 3);
                            $unitCost = (float) ($recipeLine['unit_cost'] ?? 0);
                            $materialCostTotal += $neededQuantity * $unitCost;

                            if (! isset($ingredientDemand[$ingredientId])) {
                                $ingredientDemand[$ingredientId] = [
                                    'quantity' => 0.0,
                                    'unit_cost' => $unitCost,
                                ];
                            }

                            $ingredientDemand[$ingredientId]['quantity'] += $neededQuantity;
                        }
                    }

                    foreach ($ingredientDemand as $ingredientId => $demand) {
                        $quantity = round((float) $demand['quantity'], 3);
                        $unitCost = round((float) $demand['unit_cost'], 4);

                        InventoryMovement::query()->create([
                            'ingredient_id' => $ingredientId,
                            'movement_type' => InventoryMovement::TYPE_PRODUCTION_OUT,
                            'direction' => InventoryMovement::DIRECTION_OUT,
                            'quantity' => $quantity,
                            'unit_cost' => $unitCost > 0 ? $unitCost : null,
                            'total_cost' => $unitCost > 0 ? round($quantity * $unitCost, 2) : null,
                            'occurred_at' => $order->placed_at,
                            'reference_type' => Order::class,
                            'reference_id' => $order->id,
                            'notes' => 'PRO load test alapanyag-felhasználás',
                            'created_by' => null,
                        ]);

                        Ingredient::query()
                            ->whereKey($ingredientId)
                            ->decrement('current_stock', $quantity);
                    }

                    $order->update([
                        'material_cost_total' => round($materialCostTotal, 2),
                    ]);
                }
            }
        });
    }

    /**
     * @param EloquentCollection<int, Product> $products
     * @return Collection<int, array{product: Product, quantity: int, unit_price: float, line_total: float}>
     */
    private function buildCart(EloquentCollection $products, int $targetMin, int $targetMax): Collection
    {
        $cart = collect();
        $total = 0.0;
        $attempts = 0;
        $eligibleProducts = $products
            ->filter(fn (Product $product): bool => (float) $product->price > 0 && (float) $product->price <= $targetMax)
            ->values();

        if ($eligibleProducts->isEmpty()) {
            return collect();
        }

        while ($total < $targetMin && $attempts < 50) {
            $attempts++;
            $remaining = max(0, $targetMax - $total);
            $available = $eligibleProducts
                ->filter(fn (Product $product): bool => (float) $product->price <= $remaining)
                ->values();

            if ($available->isEmpty()) {
                break;
            }

            /** @var Product $product */
            $product = $available->random();
            $unitPrice = (float) $product->price;
            $maxQty = max(1, min(3, (int) floor($remaining / $unitPrice)));
            $quantity = mt_rand(1, $maxQty);
            $lineTotal = round($unitPrice * $quantity, 2);

            $existingIndex = $cart->search(fn (array $line): bool => $line['product']->id === $product->id);
            if ($existingIndex !== false) {
                $existing = $cart->get($existingIndex);
                $existing['quantity'] += $quantity;
                $existing['line_total'] = round($existing['quantity'] * $existing['unit_price'], 2);
                $cart->put($existingIndex, $existing);
            } else {
                $cart->push([
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $total = (float) $cart->sum('line_total');
        }

        return $cart->values();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRecipeSnapshot(Product $product): array
    {
        return $product->productIngredients
            ->filter(fn ($line): bool => $line->ingredient !== null)
            ->map(function ($line): array {
                $ingredient = $line->ingredient;
                $unitCost = (float) ($ingredient->average_unit_cost
                    ?? $ingredient->estimated_unit_cost
                    ?? 0);

                return [
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'unit' => $ingredient->unit,
                    'quantity_per_unit' => (float) $line->quantity,
                    'unit_cost' => $unitCost,
                ];
            })
            ->values()
            ->all();
    }

    private function randomPickupSlot(): string
    {
        return collect(['08:00-10:00', '10:00-12:00', '12:00-14:00', '14:00-16:00'])->random();
    }

    private function resetLoadTestData(): void
    {
        DB::transaction(function (): void {
            InventoryMovement::query()
                ->where('notes', 'like', 'PRO load test%')
                ->delete();

            $purchaseIds = Purchase::query()
                ->where('reference_number', 'like', 'LT-PO-%')
                ->pluck('id');

            if ($purchaseIds->isNotEmpty()) {
                PurchaseReceipt::query()
                    ->whereIn('purchase_id', $purchaseIds)
                    ->delete();

                Purchase::query()
                    ->whereIn('id', $purchaseIds)
                    ->delete();
            }

            $orderIds = Order::query()
                ->where('order_number', 'like', 'LT-%')
                ->pluck('id');

            if ($orderIds->isNotEmpty()) {
                Order::query()
                    ->whereIn('id', $orderIds)
                    ->delete();
            }
        });
    }
}
