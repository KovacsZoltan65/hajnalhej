<?php

namespace App\Repositories;

use App\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PurchaseRepository
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

    public function findWithItems(int $purchaseId): ?Purchase
    {
        return Purchase::query()
            ->with(['supplier:id,name', 'items.ingredient:id,name,unit', 'creator:id,name,email'])
            ->find($purchaseId);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Purchase
    {
        return Purchase::query()->create($payload);
    }

    /**
     * @param array<string, mixed> $payload
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

    /**
     * @param array<string, mixed> $filters
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $supplierId = $filters['supplier_id'] ?? null;
        $sortField = (string) ($filters['sort_field'] ?? 'purchase_date');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'desc');

        $allowedSorts = ['purchase_date', 'total', 'status', 'created_at'];
        if (! \in_array($sortField, $allowedSorts, true)) {
            $sortField = 'purchase_date';
        }

        if (! \in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        return Purchase::query()
            ->with(['supplier:id,name', 'creator:id,name,email'])
            ->withCount('items')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($supplierId !== null && $supplierId !== '', fn (Builder $query): Builder => $query->where('supplier_id', (int) $supplierId))
            ->orderBy($sortField, $sortDirection)
            ->orderByDesc('id');
    }
}

