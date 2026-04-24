<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\User;
use App\Repositories\ProcurementIntelligenceRepository;
use RuntimeException;

class PurchaseDraftGenerationService
{
    private const GENERATED_NOTE = 'Automatikusan generált tervezet utánrendelési javaslatból.';

    public function __construct(
        private readonly ProcurementIntelligenceService $intelligenceService,
        private readonly ProcurementIntelligenceRepository $intelligenceRepository,
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
            throw new RuntimeException('Nincs generálható utánrendelési javaslat.');
        }

        $ingredientIds = $recommendations
            ->pluck('ingredient_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
        $ingredients = Ingredient::query()
            ->whereIn('id', $ingredientIds)
            ->get(['id', 'unit', 'estimated_unit_cost'])
            ->keyBy('id');
        $latestPurchases = $this->intelligenceRepository->latestPurchaseRowsForIngredients($ingredientIds)->keyBy('ingredient_id');
        $cheapestFreshPurchases = $this->intelligenceRepository
            ->cheapestFreshSupplierRows($ingredientIds, (int) ($payload['days'] ?? 30))
            ->keyBy('ingredient_id');

        $draftItemsBySupplier = $recommendations
            ->map(function (array $recommendation) use ($ingredients, $latestPurchases, $cheapestFreshPurchases): array {
                $ingredientId = (int) $recommendation['ingredient_id'];
                $ingredient = $ingredients->get($ingredientId);
                if ($ingredient === null) {
                    return [];
                }

                $latestPurchase = $latestPurchases->get($ingredientId);
                $cheapestFreshPurchase = $cheapestFreshPurchases->get($ingredientId);
                $supplierId = $latestPurchase?->supplier_id !== null
                    ? (int) $latestPurchase->supplier_id
                    : ($cheapestFreshPurchase?->supplier_id !== null ? (int) $cheapestFreshPurchase->supplier_id : null);
                $unitCost = $latestPurchase?->unit_cost !== null
                    ? (float) $latestPurchase->unit_cost
                    : ($cheapestFreshPurchase?->unit_cost !== null ? (float) $cheapestFreshPurchase->unit_cost : (float) ($ingredient->estimated_unit_cost ?? 0));

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
            throw new RuntimeException('Nincs generálható beszerzési tervezet.');
        }

        return $drafts;
    }
}
