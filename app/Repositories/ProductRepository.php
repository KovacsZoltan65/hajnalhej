<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ProductRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->adminQuery($filters)
            ->with([
                'category:id,name',
                'productIngredients.ingredient:id,name,unit,is_active,deleted_at',
            ])
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, array{id:int,name:string}>
     */
    public function listSelectableCategories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ]);
    }

    /**
     * @return Collection<int, array{id:int,name:string,slug:string}>
     */
    public function listSelectableActiveProducts(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->whereHas('productIngredients')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
            ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh()->load([
            'category:id,name',
            'productIngredients.ingredient:id,name,unit,is_active,deleted_at',
        ]);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Product::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * @param array<int, int> $ids
     * @return EloquentCollection<int, Product>
     */
    public function findOrderableByIds(array $ids): EloquentCollection
    {
        return Product::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->where('stock_status', '!=', Product::STOCK_OUT_OF_STOCK)
            ->with([
                'productIngredients.ingredient:id,name,unit,is_active,deleted_at',
            ])
            ->get();
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $categoryId = $filters['category_id'] ?? null;
        $isActive = $filters['is_active'] ?? null;
        $sortField = (string) ($filters['sort_field'] ?? 'sort_order');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'asc');

        $sortableFields = ['name', 'slug', 'price', 'is_active', 'sort_order'];

        if (! \in_array($sortField, $sortableFields, true)) {
            $sortField = 'sort_order';
        }

        if (! \in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $query = Product::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== null && $categoryId !== '', function (Builder $query) use ($categoryId): void {
                $query->where('category_id', (int) $categoryId);
            })
            ->when($isActive !== null && $isActive !== '', function (Builder $query) use ($isActive): void {
                $query->where('is_active', (bool) $isActive);
            });

        $query
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id');

        return $query;
    }
}
