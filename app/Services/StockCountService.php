<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\StockCount;
use App\Models\User;
use App\Repositories\StockCountRepository;
use App\Services\Audit\InventoryAuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockCountService
{
    public function __construct(
        private readonly StockCountRepository $repository,
        private readonly InventoryService $inventoryService,
        private readonly InventoryAuditService $auditService,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    public function findWithItems(int $id): ?StockCount
    {
        return $this->repository->findWithItems($id);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload, ?User $actor = null): StockCount
    {
        return DB::transaction(function () use ($payload, $actor): StockCount {
            $items = $this->normalizeItems($payload['items'] ?? []);
            if ($items === []) {
                throw new RuntimeException('Leltárhoz legalább egy tétel szükséges.');
            }

            $stockCount = $this->repository->create([
                'count_date' => $payload['count_date'] ?? Carbon::today()->toDateString(),
                'status' => StockCount::STATUS_DRAFT,
                'notes' => $this->emptyToNull($payload['notes'] ?? null),
                'created_by' => $actor?->id,
            ]);

            $this->repository->syncItems($stockCount, $items);

            return $this->repository->findWithItems($stockCount->id) ?? $stockCount;
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(StockCount $stockCount, array $payload): StockCount
    {
        if ($stockCount->status !== StockCount::STATUS_DRAFT) {
            throw new RuntimeException('Lezárt leltár nem módosítható.');
        }

        return DB::transaction(function () use ($stockCount, $payload): StockCount {
            $items = $this->normalizeItems($payload['items'] ?? []);
            if ($items === []) {
                throw new RuntimeException('Leltárhoz legalább egy tétel szükséges.');
            }

            $updated = $this->repository->update($stockCount, [
                'count_date' => $payload['count_date'] ?? $stockCount->count_date?->toDateString(),
                'notes' => $this->emptyToNull($payload['notes'] ?? null),
            ]);
            $this->repository->syncItems($updated, $items);

            return $this->repository->findWithItems($updated->id) ?? $updated;
        });
    }

    public function close(StockCount $stockCount, ?User $actor = null): StockCount
    {
        if ($stockCount->status !== StockCount::STATUS_DRAFT) {
            throw new RuntimeException('Csak draft leltár zárható.');
        }

        return DB::transaction(function () use ($stockCount, $actor): StockCount {
            $stockCount->loadMissing('items');

            foreach ($stockCount->items as $item) {
                $difference = (float) $item->difference;
                if (abs($difference) < 0.0001) {
                    continue;
                }

                $this->inventoryService->createMovement([
                    'ingredient_id' => $item->ingredient_id,
                    'movement_type' => InventoryMovement::TYPE_COUNT_CORRECTION,
                    'direction' => $difference >= 0 ? InventoryMovement::DIRECTION_IN : InventoryMovement::DIRECTION_OUT,
                    'quantity' => abs($difference),
                    'occurred_at' => now(),
                    'reference_type' => 'stock_count',
                    'reference_id' => $stockCount->id,
                    'notes' => 'Leltárkorrekció',
                ], $actor);
            }

            $updated = $this->repository->update($stockCount, [
                'status' => StockCount::STATUS_CLOSED,
                'closed_at' => now(),
            ]);

            $this->auditService->logStockCountClosed($updated, $actor);

            return $this->repository->findWithItems($updated->id) ?? $updated;
        });
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            $expected = round((float) ($item['expected_quantity'] ?? 0), 3);
            $counted = round((float) ($item['counted_quantity'] ?? 0), 3);
            $ingredientId = (int) ($item['ingredient_id'] ?? 0);

            if ($ingredientId <= 0) {
                continue;
            }

            $normalized[] = [
                'ingredient_id' => $ingredientId,
                'expected_quantity' => number_format($expected, 3, '.', ''),
                'counted_quantity' => number_format($counted, 3, '.', ''),
                'difference' => number_format($counted - $expected, 3, '.', ''),
            ];
        }

        return $normalized;
    }

    private function emptyToNull(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}

