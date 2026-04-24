<?php

namespace App\Repositories;

use App\Models\StockCount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StockCountRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->adminQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findWithItems(int $id): ?StockCount
    {
        return StockCount::query()
            ->with(['items.ingredient:id,name,unit', 'creator:id,name,email'])
            ->find($id);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): StockCount
    {
        return StockCount::query()->create($payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(StockCount $stockCount, array $payload): StockCount
    {
        $stockCount->update($payload);

        return $stockCount->refresh();
    }

    public function syncItems(StockCount $stockCount, array $items): void
    {
        $stockCount->items()->delete();
        $stockCount->items()->createMany($items);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function adminQuery(array $filters): Builder
    {
        $status = trim((string) ($filters['status'] ?? ''));
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        return StockCount::query()
            ->with(['creator:id,name,email'])
            ->withCount('items')
            ->when($status !== '', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($dateFrom !== null && $dateFrom !== '', fn (Builder $query): Builder => $query->whereDate('count_date', '>=', (string) $dateFrom))
            ->when($dateTo !== null && $dateTo !== '', fn (Builder $query): Builder => $query->whereDate('count_date', '<=', (string) $dateTo))
            ->orderByDesc('count_date')
            ->orderByDesc('id');
    }
}

