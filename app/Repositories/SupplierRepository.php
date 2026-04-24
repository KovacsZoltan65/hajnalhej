<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SupplierRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->adminQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Supplier
    {
        return Supplier::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier->refresh();
    }

    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }

    /**
     * @param array<string, mixed> $filters
     * @return Builder
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $sortField = (string) ($filters['sort_field'] ?? 'name');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'asc');

        $allowedSorts = ['name', 'lead_time_days', 'created_at'];
        if (! \in_array($sortField, $allowedSorts, true)) {
            $sortField = 'name';
        }

        if (! \in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $query = Supplier::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('tax_number', 'like', "%{$search}%");
                });
            })
            ->withCount('purchases');

            $query
                ->orderBy($sortField, $sortDirection)
                ->orderBy('id');

            return $query;
    }
}
