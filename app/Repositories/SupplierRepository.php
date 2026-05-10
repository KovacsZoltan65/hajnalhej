<?php

namespace App\Repositories;

use App\Data\Suppliers\SupplierIndexData;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SupplierRepository
{
    public function paginateForAdmin(SupplierIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Supplier
    {
        return Supplier::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
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

    private function adminQuery(SupplierIndexData $filters): Builder
    {
        $query = Supplier::query()
            ->when($filters->search !== null, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $inner) use ($filters): void {
                    $inner
                        ->where('name', 'like', "%{$filters->search}%")
                        ->orWhere('email', 'like', "%{$filters->search}%")
                        ->orWhere('phone', 'like', "%{$filters->search}%")
                        ->orWhere('tax_number', 'like', "%{$filters->search}%");
                });
            })
            ->withCount('purchases');

        $query
            ->orderBy($filters->sort_field, $filters->sort_direction)
            ->orderBy('id');

        return $query;
    }
}
