<?php

namespace App\Repositories;

use App\Data\Suppliers\SupplierIndexData;
use App\Models\Supplier;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\Cache\CacheVersionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;

class SupplierRepository
{
    public function __construct(
        private readonly CacheVersionService $cacheVersionService,
    ) {}

    public function paginateForAdmin(SupplierIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    /**
     * @return EloquentCollection<int, Supplier>
     */
    public function listSelectable(?bool $active = null): EloquentCollection
    {
        $version = $this->cacheVersionService->get(CacheNamespaces::SELECTORS_SUPPLIERS);
        $key = CacheKeyService::make(CacheNamespaces::SELECTORS_SUPPLIERS, $version, [
            'active' => $active,
            'locale' => app()->getLocale(),
        ]);

        return Cache::remember($key, now()->addMinutes(30), fn (): EloquentCollection => Supplier::query()
            ->when($active !== null, fn (Builder $query): Builder => $query->where('active', $active))
            ->orderBy('name')
            ->get(['id', 'name']));
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
