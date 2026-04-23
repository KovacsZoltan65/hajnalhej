<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\User;
use App\Repositories\InventoryMovementRepository;
use App\Services\Audit\InventoryAuditService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function __construct(
        private readonly InventoryMovementRepository $movementRepository,
        private readonly InventoryAuditService $auditService,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createMovement(array $payload, ?User $actor = null): InventoryMovement
    {
        /** @var InventoryMovement $movement */
        $movement = DB::transaction(function () use ($payload, $actor): InventoryMovement {
            $ingredientId = (int) $payload['ingredient_id'];
            $ingredient = Ingredient::query()->lockForUpdate()->findOrFail($ingredientId);

            $direction = (string) $payload['direction'];
            $quantity = $this->toQuantity($payload['quantity'] ?? 0);

            $currentStock = (float) $ingredient->current_stock;
            $currentStockValue = (float) ($ingredient->stock_value ?? 0);
            $currentAverageCost = (float) ($ingredient->average_unit_cost ?? $ingredient->estimated_unit_cost ?? 0);

            $unitCost = $payload['unit_cost'] ?? null;
            $totalCost = $payload['total_cost'] ?? null;

            if ($direction === InventoryMovement::DIRECTION_IN) {
                $totalCostValue = $totalCost !== null ? (float) $totalCost : $quantity * (float) ($unitCost ?? $currentAverageCost);
                $unitCostValue = $quantity > 0 ? ($totalCostValue / $quantity) : (float) ($unitCost ?? $currentAverageCost);
                $newStock = $currentStock + $quantity;
                $newStockValue = $currentStockValue + $totalCostValue;
            } else {
                $unitCostValue = (float) ($unitCost ?? $currentAverageCost);
                $totalCostValue = $totalCost !== null ? (float) $totalCost : $quantity * $unitCostValue;
                $newStock = $currentStock - $quantity;
                $newStockValue = $currentStockValue - $totalCostValue;
            }

            $newAverageCost = $newStock > 0 ? $newStockValue / $newStock : 0.0;
            if ($newStock <= 0) {
                $newStockValue = 0.0;
                $newAverageCost = 0.0;
            }

            $movement = $this->movementRepository->create([
                'ingredient_id' => $ingredient->id,
                'movement_type' => (string) $payload['movement_type'],
                'direction' => $direction,
                'quantity' => number_format($quantity, 3, '.', ''),
                'unit_cost' => number_format($unitCostValue, 4, '.', ''),
                'total_cost' => number_format($totalCostValue, 2, '.', ''),
                'occurred_at' => $payload['occurred_at'] ?? Carbon::now(),
                'reference_type' => $payload['reference_type'] ?? null,
                'reference_id' => $payload['reference_id'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'created_by' => $actor?->id ?? ($payload['created_by'] ?? null),
            ]);

            $ingredient->update([
                'current_stock' => number_format(max($newStock, 0), 3, '.', ''),
                'stock_value' => number_format(max($newStockValue, 0), 2, '.', ''),
                'average_unit_cost' => number_format(max($newAverageCost, 0), 4, '.', ''),
            ]);

            return $movement->refresh();
        });

        return $movement;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordWaste(array $payload, ?User $actor = null): InventoryMovement
    {
        $wasteType = (string) ($payload['waste_type'] ?? 'ingredient');
        if ($wasteType === 'product') {
            return $this->recordProductWaste($payload, $actor);
        }

        $movement = $this->createMovement([
            'ingredient_id' => $payload['ingredient_id'],
            'movement_type' => InventoryMovement::TYPE_WASTE_OUT,
            'direction' => InventoryMovement::DIRECTION_OUT,
            'quantity' => $payload['quantity'],
            'occurred_at' => $payload['occurred_at'] ?? now(),
            'reference_type' => 'waste',
            'reference_id' => null,
            'notes' => $payload['reason'] ?? ($payload['notes'] ?? null),
        ], $actor);

        $this->auditService->logWasteRecorded($movement, $actor);

        return $movement;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recordProductWaste(array $payload, ?User $actor = null): InventoryMovement
    {
        $productId = (int) ($payload['product_id'] ?? 0);
        $wastedProductQuantity = $this->toQuantity($payload['quantity'] ?? 0);

        /** @var Product $product */
        $product = Product::query()
            ->with([
                'productIngredients' => static fn ($query) => $query
                    ->select(['id', 'product_id', 'ingredient_id', 'quantity', 'sort_order'])
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'productIngredients.ingredient:id,name,unit',
            ])
            ->findOrFail($productId);

        /** @var EloquentCollection<int, ProductIngredient> $bomItems */
        $bomItems = $product->productIngredients;
        if ($bomItems->isEmpty()) {
            throw new RuntimeException('A kiválasztott termékhez nincs recept/BOM tétel, ezért nem selejtezhető.');
        }

        $notesPrefix = sprintf(
            'Termék selejt: %s (%s db), ok: %s',
            $product->name,
            number_format($wastedProductQuantity, 3, '.', ''),
            (string) ($payload['reason'] ?? '-'),
        );

        $firstMovement = null;

        foreach ($bomItems as $bomItem) {
            $ingredientWasteQuantity = $wastedProductQuantity * (float) $bomItem->quantity;
            if ($ingredientWasteQuantity <= 0) {
                continue;
            }

            $movement = $this->createMovement([
                'ingredient_id' => $bomItem->ingredient_id,
                'movement_type' => InventoryMovement::TYPE_WASTE_OUT,
                'direction' => InventoryMovement::DIRECTION_OUT,
                'quantity' => $ingredientWasteQuantity,
                'occurred_at' => $payload['occurred_at'] ?? now(),
                'reference_type' => 'product_waste',
                'reference_id' => $product->id,
                'notes' => sprintf(
                    '%s; alapanyag: %s',
                    $notesPrefix,
                    $bomItem->ingredient?->name ?? ('#'.$bomItem->ingredient_id),
                ),
            ], $actor);

            $this->auditService->logWasteRecorded($movement, $actor);
            $firstMovement ??= $movement;
        }

        if (! $firstMovement instanceof InventoryMovement) {
            throw new RuntimeException('A termék selejt könyvelése nem hozott létre készletmozgást.');
        }

        return $firstMovement;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordAdjustment(array $payload, ?User $actor = null): InventoryMovement
    {
        $difference = (float) ($payload['difference'] ?? 0);
        $direction = $difference >= 0 ? InventoryMovement::DIRECTION_IN : InventoryMovement::DIRECTION_OUT;
        $movementType = $difference >= 0 ? InventoryMovement::TYPE_ADJUSTMENT_IN : InventoryMovement::TYPE_ADJUSTMENT_OUT;

        $movement = $this->createMovement([
            'ingredient_id' => $payload['ingredient_id'],
            'movement_type' => $movementType,
            'direction' => $direction,
            'quantity' => abs($difference),
            'unit_cost' => $payload['unit_cost'] ?? null,
            'occurred_at' => $payload['occurred_at'] ?? now(),
            'reference_type' => $payload['reference_type'] ?? 'adjustment',
            'reference_id' => $payload['reference_id'] ?? null,
            'notes' => $payload['notes'] ?? null,
        ], $actor);

        $this->auditService->logInventoryAdjusted($movement, $actor);

        return $movement;
    }

    private function toQuantity(mixed $value): float
    {
        return max((float) $value, 0.0);
    }
}
