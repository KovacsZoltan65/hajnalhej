<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\User;
use App\Repositories\InventoryMovementRepository;
use App\Services\Audit\InventoryAuditService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductionInventoryService
{
    public function __construct(
        private readonly InventoryMovementRepository $movementRepository,
        private readonly InventoryService $inventoryService,
        private readonly InventoryAuditService $auditService,
    ) {
    }

    public function consumeForOrder(Order $order, ?User $actor = null): float
    {
        if ($this->movementRepository->existsForReference('order', $order->id, InventoryMovement::TYPE_PRODUCTION_OUT)) {
            return (float) $order->material_cost_total;
        }

        return DB::transaction(function () use ($order, $actor): float {
            $order->loadMissing('items.product.productIngredients.ingredient');
            $ingredientUsage = [];

            foreach ($order->items as $item) {
                if ($item->product === null) {
                    continue;
                }

                foreach ($item->product->productIngredients as $bomRow) {
                    $ingredientId = (int) $bomRow->ingredient_id;
                    $ingredientUsage[$ingredientId] = ($ingredientUsage[$ingredientId] ?? 0.0) + ((float) $bomRow->quantity * (int) $item->quantity);
                }
            }

            $totalMaterialCost = 0.0;

            foreach ($ingredientUsage as $ingredientId => $requiredQty) {
                /** @var Ingredient $ingredient */
                $ingredient = Ingredient::query()->lockForUpdate()->findOrFail($ingredientId);
                $available = (float) $ingredient->current_stock;

                if ($available < $requiredQty && (bool) config('inventory.block_on_shortage', false)) {
                    throw new RuntimeException(__('admin_inventory.not_enough_stock') . ": {$ingredient->name}");
                }

                $movement = $this->inventoryService->createMovement([
                    'ingredient_id' => $ingredientId,
                    'movement_type' => InventoryMovement::TYPE_PRODUCTION_OUT,
                    'direction' => InventoryMovement::DIRECTION_OUT,
                    'quantity' => $requiredQty,
                    'occurred_at' => now(),
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'notes' => 'BOM fogyás rendelés teljesítéshez',
                ], $actor);

                if ($available < $requiredQty) {
                    $this->auditService->logInventoryShortageDetected($movement, $actor, $available);
                }

                $totalMaterialCost += (float) ($movement->total_cost ?? 0);
            }

            $order->update([
                'material_cost_total' => number_format($totalMaterialCost, 2, '.', ''),
            ]);

            return round($totalMaterialCost, 2);
        });
    }
}

