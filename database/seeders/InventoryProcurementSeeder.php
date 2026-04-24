<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use App\Services\StockCountService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InventoryProcurementSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::query()->where('email', 'admin@hajnalhej.hu')->first()
            ?? User::query()->first();

        $ingredients = Ingredient::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->limit(6)
            ->get();

        if ($ingredients->count() < 3) {
            return;
        }

        $suppliers = [
            Supplier::query()->updateOrCreate(
                ['name' => 'Malom Kft.'],
                ['email' => 'rendeles@malomkft.hu', 'phone' => '+36 1 555 0101', 'tax_number' => '12345678-2-41', 'notes' => 'Liszt és gabona alapanyagok'],
            ),
            Supplier::query()->updateOrCreate(
                ['name' => 'Tejtermék Partner Zrt.'],
                ['email' => 'info@tejpartner.hu', 'phone' => '+36 1 555 0202', 'tax_number' => '87654321-2-41', 'notes' => 'Vaj, tej, tojás'],
            ),
            Supplier::query()->updateOrCreate(
                ['name' => 'Fűszer Nagyker'],
                ['email' => 'sales@fuszernagyker.hu', 'phone' => '+36 1 555 0303', 'tax_number' => '13572468-2-41', 'notes' => 'Só, cukor, fűszerek'],
            ),
        ];

        $purchaseService = app(PurchaseService::class);
        $inventoryService = app(InventoryService::class);
        $stockCountService = app(StockCountService::class);

        $postedPurchase = $this->upsertPurchase(
            supplier: $suppliers[0],
            reference: 'SEED-PO-2026-001',
            purchaseDate: Carbon::today()->subDays(2)->toDateString(),
            ingredients: [
                ['ingredient' => $ingredients[0], 'quantity' => 40, 'unit_cost' => 280],
                ['ingredient' => $ingredients[1], 'quantity' => 25, 'unit_cost' => 340],
            ],
            actorId: $actor?->id,
        );

        if ($postedPurchase->status === Purchase::STATUS_DRAFT) {
            $purchaseService->post($postedPurchase, $actor);
        }

        $this->upsertPurchase(
            supplier: $suppliers[1],
            reference: 'SEED-PO-2026-002',
            purchaseDate: Carbon::today()->subDay()->toDateString(),
            ingredients: [
                ['ingredient' => $ingredients[2], 'quantity' => 15, 'unit_cost' => 420],
                ['ingredient' => $ingredients[0], 'quantity' => 20, 'unit_cost' => 295],
            ],
            actorId: $actor?->id,
        );

        $wasteNote = 'Seeder selejt mozgás';
        $wasteExists = InventoryMovement::query()
            ->where('movement_type', InventoryMovement::TYPE_WASTE_OUT)
            ->where('notes', $wasteNote)
            ->exists();

        if (! $wasteExists) {
            $inventoryService->recordWaste([
                'ingredient_id' => $ingredients[0]->id,
                'quantity' => 2.5,
                'reason' => $wasteNote,
                'occurred_at' => Carbon::today()->subDay()->toDateTimeString(),
            ], $actor);
        }

        $countExists = \App\Models\StockCount::query()
            ->where('notes', 'Seeder leltár')
            ->exists();

        if (! $countExists) {
            $stockCount = $stockCountService->create([
                'count_date' => Carbon::today()->toDateString(),
                'notes' => 'Seeder leltár',
                'items' => [
                    [
                        'ingredient_id' => $ingredients[0]->id,
                        'expected_quantity' => (float) $ingredients[0]->current_stock,
                        'counted_quantity' => max((float) $ingredients[0]->current_stock - 1, 0),
                    ],
                    [
                        'ingredient_id' => $ingredients[1]->id,
                        'expected_quantity' => (float) $ingredients[1]->current_stock,
                        'counted_quantity' => (float) $ingredients[1]->current_stock + 0.5,
                    ],
                ],
            ], $actor);

            $stockCountService->close($stockCount, $actor);
        }
    }

    /**
     * @param array<int, array{ingredient:Ingredient,quantity:float,unit_cost:float}> $ingredients
     */
    private function upsertPurchase(
        Supplier $supplier,
        string $reference,
        string $purchaseDate,
        array $ingredients,
        ?int $actorId,
    ): Purchase {
        $purchase = Purchase::query()->firstOrNew(['reference_number' => $reference]);
        $purchase->supplier_id = $supplier->id;
        $purchase->purchase_date = $purchaseDate;
        $purchase->status = $purchase->status ?: Purchase::STATUS_DRAFT;
        $purchase->notes = 'Seeder beszerzés';
        $purchase->created_by = $purchase->created_by ?? $actorId;
        $purchase->subtotal = 0;
        $purchase->total = 0;
        $purchase->save();

        $lineItems = [];
        $total = 0.0;

        foreach ($ingredients as $row) {
            $quantity = round((float) $row['quantity'], 3);
            $unitCost = round((float) $row['unit_cost'], 4);
            $lineTotal = round($quantity * $unitCost, 2);
            $total += $lineTotal;

            $lineItems[] = [
                'ingredient_id' => $row['ingredient']->id,
                'quantity' => number_format($quantity, 3, '.', ''),
                'unit' => $row['ingredient']->unit,
                'unit_cost' => number_format($unitCost, 4, '.', ''),
                'line_total' => number_format($lineTotal, 2, '.', ''),
            ];
        }

        $purchase->items()->delete();
        $purchase->items()->createMany($lineItems);
        $purchase->update([
            'subtotal' => number_format($total, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
        ]);

        return $purchase->refresh();
    }
}

