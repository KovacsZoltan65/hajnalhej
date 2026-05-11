<?php

namespace App\Repositories;

use App\Data\StockCounts\StockCountIndexData;
use App\Models\StockCount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StockCountRepository
{
    public function paginateForAdmin(StockCountIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    public function findWithItems(int $id): ?StockCount
    {
        return StockCount::query()
            ->with(['items.ingredient:id,name,unit', 'creator:id,name,email'])
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): StockCount
    {
        return StockCount::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
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

    private function adminQuery(StockCountIndexData $filters): Builder
    {
        return StockCount::query()
            ->with(['creator:id,name,email'])
            ->withCount('items')
            ->when($filters->status !== null, fn (Builder $query): Builder => $query->where('status', $filters->status))
            ->when($filters->date_from !== null, fn (Builder $query): Builder => $query->whereDate('count_date', '>=', $filters->date_from))
            ->when($filters->date_to !== null, fn (Builder $query): Builder => $query->whereDate('count_date', '<=', $filters->date_to))
            ->orderByDesc('count_date')
            ->orderByDesc('id');
    }
}
