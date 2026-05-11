<?php

namespace App\Repositories;

use App\Data\Purchases\PurchaseIndexData;
use App\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PurchaseRepository
{
    public function paginateForAdmin(PurchaseIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    public function findWithItems(int $purchaseId): ?Purchase
    {
        return Purchase::query()
            ->with(['supplier:id,name', 'items.ingredient:id,name,unit', 'creator:id,name,email'])
            ->find($purchaseId);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): Purchase
    {
        return Purchase::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(Purchase $purchase, array $payload): Purchase
    {
        $purchase->update($payload);

        return $purchase->refresh();
    }

    public function syncItems(Purchase $purchase, array $items): void
    {
        $purchase->items()->delete();
        $purchase->items()->createMany($items);
    }

    private function adminQuery(PurchaseIndexData $filters): Builder
    {
        return Purchase::query()
            ->with(['supplier:id,name', 'creator:id,name,email'])
            ->withCount('items')
            ->when($filters->search !== null, function (Builder $query) use ($filters): void {
                $search = (string) $filters->search;

                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($filters->status !== null, fn (Builder $query): Builder => $query->where('status', $filters->status))
            ->when($filters->supplier_id !== null, fn (Builder $query): Builder => $query->where('supplier_id', $filters->supplier_id))
            ->orderBy($filters->sort_field, $filters->sort_direction)
            ->orderByDesc('id');
    }
}
