<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProWasteLoadTestSeeder extends Seeder
{
    /**
     * Selejtezési tesztadat generátor.
     *
     * Kétféle selejtet készít:
     * - termékhez kötött selejtet a rendelési tételek recipe_snapshot mezője alapján,
     * - közvetlen alapanyag selejtet romlás/sérülés/lejárat szimulálására.
     */
    public function run(): void
    {
        if (! filter_var(env('HH_LOAD_TEST_WASTE_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        mt_srand((int) env('HH_LOAD_TEST_WASTE_SEED', ((int) env('HH_LOAD_TEST_SEED', 20260427)) + 17));

        $days = max(1, (int) env('HH_LOAD_TEST_DAYS', 30));
        $startDate = Carbon::parse(env('HH_LOAD_TEST_START_DATE', Carbon::today()->subDays($days - 1)->toDateString()))->startOfDay();
        $endDate = $startDate->copy()->addDays($days - 1)->endOfDay();

        $admin = User::query()->where('email', 'admin@hajnalhej.hu')->first()
            ?? User::query()->first();

        DB::transaction(function () use ($startDate, $endDate, $admin): void {
            $this->removePreviousWasteMovements($startDate, $endDate);
            $this->createProductWaste($startDate, $endDate, $admin?->id);
            $this->createIngredientWaste($startDate, $endDate, $admin?->id);
        });
    }

    private function removePreviousWasteMovements(Carbon $startDate, Carbon $endDate): void
    {
        InventoryMovement::query()
            ->where('movement_type', InventoryMovement::TYPE_WASTE_OUT)
            ->where('notes', 'like', 'PRO load test selejt%')
            ->whereBetween('occurred_at', [$startDate, $endDate->copy()->addDays(2)])
            ->delete();
    }

    private function createProductWaste(Carbon $startDate, Carbon $endDate, ?int $adminId): void
    {
        $dailyChance = $this->percent('HH_LOAD_TEST_PRODUCT_WASTE_DAILY_CHANCE', 65);
        $orderItemChance = $this->percent('HH_LOAD_TEST_PRODUCT_WASTE_ITEM_CHANCE', 18);
        $minWasteRate = max(0.0, (float) env('HH_LOAD_TEST_PRODUCT_WASTE_RATE_MIN', 0.03));
        $maxWasteRate = max($minWasteRate, (float) env('HH_LOAD_TEST_PRODUCT_WASTE_RATE_MAX', 0.12));

        $ordersByDay = Order::query()
            ->with(['items'])
            ->where('order_number', 'like', 'LT-%')
            ->whereBetween('placed_at', [$startDate, $endDate])
            ->orderBy('placed_at')
            ->get()
            ->groupBy(fn (Order $order): string => Carbon::parse($order->placed_at)->toDateString());

        foreach ($ordersByDay as $date => $orders) {
            if (! $this->hits($dailyChance)) {
                continue;
            }

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if (! $this->hits($orderItemChance)) {
                        continue;
                    }

                    $wastedProductQuantity = $this->randomDecimal(
                        max(0.10, (float) $item->quantity * $minWasteRate),
                        max(0.20, (float) $item->quantity * $maxWasteRate),
                        3,
                    );

                    if ($wastedProductQuantity <= 0) {
                        continue;
                    }

                    $recipeSnapshot = $item->recipe_snapshot ?? [];
                    if (! is_array($recipeSnapshot) || $recipeSnapshot === []) {
                        continue;
                    }

                    foreach ($recipeSnapshot as $recipeLine) {
                        $ingredientId = (int) ($recipeLine['ingredient_id'] ?? 0);
                        $quantityPerUnit = (float) ($recipeLine['quantity_per_unit'] ?? 0);

                        if ($ingredientId <= 0 || $quantityPerUnit <= 0) {
                            continue;
                        }

                        $wasteQuantity = round($quantityPerUnit * $wastedProductQuantity, 3);
                        $this->createWasteMovement(
                            ingredientId: $ingredientId,
                            quantity: $wasteQuantity,
                            occurredAt: Carbon::parse($order->completed_at ?? $order->placed_at)->copy()->addMinutes(mt_rand(20, 180)),
                            notes: sprintf(
                                'PRO load test selejt - termék selejt; rendelés: %s; termék: %s; selejtezett mennyiség: %s db; ok: %s',
                                $order->order_number,
                                $item->product_name_snapshot,
                                number_format($wastedProductQuantity, 3, '.', ''),
                                $this->randomProductWasteReason(),
                            ),
                            referenceType: 'load_test_product_waste',
                            referenceId: $item->id,
                            adminId: $adminId,
                        );
                    }
                }
            }
        }
    }

    private function createIngredientWaste(Carbon $startDate, Carbon $endDate, ?int $adminId): void
    {
        $dailyChance = $this->percent('HH_LOAD_TEST_INGREDIENT_WASTE_DAILY_CHANCE', 45);
        $ingredientChance = $this->percent('HH_LOAD_TEST_INGREDIENT_WASTE_ITEM_CHANCE', 12);
        $minRate = max(0.0, (float) env('HH_LOAD_TEST_INGREDIENT_WASTE_RATE_MIN', 0.002));
        $maxRate = max($minRate, (float) env('HH_LOAD_TEST_INGREDIENT_WASTE_RATE_MAX', 0.015));

        /** @var \Illuminate\Database\Eloquent\Collection<int, Ingredient> $ingredients */
        $ingredients = Ingredient::query()
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('id')
            ->get();

        if ($ingredients->isEmpty()) {
            return;
        }

        $days = $startDate->diffInDays($endDate) + 1;

        for ($offset = 0; $offset < $days; $offset++) {
            if (! $this->hits($dailyChance)) {
                continue;
            }

            $date = $startDate->copy()->addDays($offset);

            foreach ($ingredients->shuffle()->take(max(1, (int) ceil($ingredients->count() * 0.25))) as $ingredient) {
                if (! $this->hits($ingredientChance)) {
                    continue;
                }

                $currentStock = (float) Ingredient::query()
                    ->whereKey($ingredient->id)
                    ->value('current_stock');

                if ($currentStock <= 0) {
                    continue;
                }

                $wasteQuantity = $this->randomDecimal($currentStock * $minRate, $currentStock * $maxRate, 3);
                $wasteQuantity = min($wasteQuantity, $currentStock);

                if ($wasteQuantity <= 0) {
                    continue;
                }

                $this->createWasteMovement(
                    ingredientId: $ingredient->id,
                    quantity: $wasteQuantity,
                    occurredAt: $date->copy()->setTime(mt_rand(6, 18), mt_rand(0, 59)),
                    notes: sprintf(
                        'PRO load test selejt - alapanyag selejt; alapanyag: %s; ok: %s',
                        $ingredient->name,
                        $this->randomIngredientWasteReason(),
                    ),
                    referenceType: 'load_test_ingredient_waste',
                    referenceId: null,
                    adminId: $adminId,
                );
            }
        }
    }

    private function createWasteMovement(
        int $ingredientId,
        float $quantity,
        Carbon $occurredAt,
        string $notes,
        string $referenceType,
        ?int $referenceId,
        ?int $adminId,
    ): void {
        if ($quantity <= 0) {
            return;
        }

        /** @var Ingredient|null $ingredient */
        $ingredient = Ingredient::query()->lockForUpdate()->find($ingredientId);
        if (! $ingredient) {
            return;
        }

        $currentStock = (float) $ingredient->current_stock;
        if ($currentStock <= 0) {
            return;
        }

        $quantity = round(min($quantity, $currentStock), 3);
        if ($quantity <= 0) {
            return;
        }

        $unitCost = (float) ($ingredient->average_unit_cost ?? $ingredient->estimated_unit_cost ?? 0);
        $totalCost = round($quantity * $unitCost, 2);
        $newStock = max(0.0, $currentStock - $quantity);
        $newStockValue = max(0.0, (float) ($ingredient->stock_value ?? 0) - $totalCost);
        $newAverageCost = $newStock > 0 ? ($newStockValue / $newStock) : 0.0;

        InventoryMovement::query()->create([
            'ingredient_id' => $ingredient->id,
            'movement_type' => InventoryMovement::TYPE_WASTE_OUT,
            'direction' => InventoryMovement::DIRECTION_OUT,
            'quantity' => $quantity,
            'unit_cost' => $unitCost > 0 ? round($unitCost, 4) : null,
            'total_cost' => $unitCost > 0 ? $totalCost : null,
            'occurred_at' => $occurredAt,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => $adminId,
        ]);

        $ingredient->update([
            'current_stock' => number_format($newStock, 3, '.', ''),
            'stock_value' => number_format($newStockValue, 2, '.', ''),
            'average_unit_cost' => number_format(max($newAverageCost, 0), 4, '.', ''),
        ]);
    }

    private function percent(string $envKey, int $default): int
    {
        return max(0, min(100, (int) env($envKey, $default)));
    }

    private function hits(int $percent): bool
    {
        return mt_rand(1, 100) <= $percent;
    }

    private function randomDecimal(float $min, float $max, int $precision): float
    {
        if ($max <= $min) {
            return round(max(0.0, $min), $precision);
        }

        $scale = 10 ** $precision;

        return round(mt_rand((int) floor($min * $scale), (int) ceil($max * $scale)) / $scale, $precision);
    }

    private function randomProductWasteReason(): string
    {
        $reasons = [
            'túlsütés',
            'sérült termék',
            'minőségellenőrzésen kiesett',
            'túltermelés',
            'csomagolási sérülés',
        ];

        return $reasons[array_rand($reasons)];
    }

    private function randomIngredientWasteReason(): string
    {
        $reasons = [
            'lejárat közeli alapanyag',
            'sérült csomagolás',
            'minőségromlás',
            'előkészítési veszteség',
            'raktári selejt',
        ];

        return $reasons[array_rand($reasons)];
    }
}
