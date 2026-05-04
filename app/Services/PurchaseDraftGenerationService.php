<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\User;
use RuntimeException;

class PurchaseDraftGenerationService
{
    private const GENERATED_NOTE = 'Automatikusan generált tervezet utánrendelési javaslatból.';

    public function __construct(
        private readonly ProcurementIntelligenceService $intelligenceService,
        private readonly PurchaseService $purchaseService,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, Purchase>
     */
    public function generateFromRecommendations(array $payload, ?User $actor = null): array
    {
        $recommendations = collect($this->intelligenceService->minimumStockRecommendationsForFilters($payload));
        $selectedIds = collect($payload['ingredient_ids'] ?? [])
            ->map(static fn (mixed $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isNotEmpty()) {
            $recommendations = $recommendations->whereIn('ingredient_id', $selectedIds->all());
        } elseif (($payload['ingredient_id'] ?? null) !== null && $payload['ingredient_id'] !== '') {
            $recommendations = $recommendations->where('ingredient_id', (int) $payload['ingredient_id']);
        }

        $recommendations = $recommendations
            ->filter(static fn (array $row): bool => (float) $row['suggested_order_quantity'] > 0)
            ->values();

        if ($recommendations->isEmpty()) {
            throw new RuntimeException(__('admin_purchase_draft.no_reorder_proposal') . '.');
        }

        $ingredientIds = $recommendations
            ->pluck('ingredient_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
        $ingredients = Ingredient::query()
            ->whereIn('id', $ingredientIds)
            ->get(['id', 'unit', 'estimated_unit_cost'])
            ->keyBy('id');

        $draftItemsBySupplier = $recommendations
            ->map(function (array $recommendation) use ($ingredients): array {
                $ingredientId = (int) $recommendation['ingredient_id'];
                $ingredient = $ingredients->get($ingredientId);
                if ($ingredient === null) {
                    return [];
                }

                $supplierId = $recommendation['recommended_supplier_id'] !== null ? (int) $recommendation['recommended_supplier_id'] : null;
                $unitCost = $recommendation['unit_cost'] !== null ? (float) $recommendation['unit_cost'] : (float) ($ingredient->estimated_unit_cost ?? 0);

                return [
                    'supplier_key' => $supplierId !== null ? (string) $supplierId : 'none',
                    'supplier_id' => $supplierId,
                    'item' => [
                        'ingredient_id' => $ingredientId,
                        'quantity' => (float) $recommendation['suggested_order_quantity'],
                        'unit' => (string) $ingredient->unit,
                        'unit_cost' => $unitCost,
                    ],
                ];
            })
            ->filter(static fn (array $row): bool => $row !== [])
            ->groupBy('supplier_key');

        $drafts = [];
        foreach ($draftItemsBySupplier as $group) {
            $first = $group->first();
            $items = $group->pluck('item')->values()->all();
            if ($items === []) {
                continue;
            }

            $drafts[] = $this->purchaseService->create([
                'supplier_id' => $first['supplier_id'],
                'purchase_date' => now()->toDateString(),
                'notes' => self::GENERATED_NOTE,
                'items' => $items,
            ], $actor);
        }

        if ($drafts === []) {
            throw new RuntimeException(__('admin_purchase_draft.no_purchase_draft') . '.');
        }

        return $drafts;
    }
}
