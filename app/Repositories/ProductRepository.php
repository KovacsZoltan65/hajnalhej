<?php

namespace App\Repositories;

use App\Data\Products\ProductIndexData;
use App\Data\Products\ProductListItemData;
use App\Models\Category;
use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\Cache\CacheVersionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    public function __construct(
        private readonly CacheVersionService $cacheVersionService,
    ) {}

    public function paginate(ProductIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->with([
                'category:id,name',
                'productIngredients.ingredient:id,name,unit,is_active,deleted_at',
            ])
            ->paginate($filters->per_page)
            ->withQueryString()
            ->through(fn (Product $product): array => ProductListItemData::from($product)->toArray());
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
        $version = $this->cacheVersionService->get(CacheNamespaces::SELECTORS_PRODUCTS);
        $key = CacheKeyService::make(CacheNamespaces::SELECTORS_PRODUCTS, $version, [
            'active' => true,
            'fields' => ['id', 'name', 'slug'],
            'has_product_ingredients' => true,
            'locale' => app()->getLocale(),
            'sort' => ['sort_order', 'name'],
            'without_trashed' => true,
        ]);

        return Cache::remember($key, now()->addMinutes(30), fn (): Collection => Product::query()
            ->where('is_active', true)
            ->whereHas('productIngredients')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
            ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
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
     * @param  array<int, int>  $ids
     * @return EloquentCollection<int, Product>
     */
    public function findOrderableByIds(array $ids): EloquentCollection
    {
        return Product::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->where('stock_status', '!=', Product::STOCK_OUT_OF_STOCK)
            ->whereHas('weeklyMenuItems', function (Builder $query): void {
                $query
                    ->where('is_active', true)
                    ->whereHas('weeklyMenu', fn (Builder $menuQuery): Builder => $menuQuery
                        ->where('status', WeeklyMenu::STATUS_PUBLISHED)
                        ->whereNotNull('published_at'));
            })
            ->with([
                'productIngredients.ingredient:id,name,unit,is_active,deleted_at',
            ])
            ->get();
    }

    private function adminQuery(ProductIndexData $filters): Builder
    {
        $search = $filters->search;
        $categoryId = $filters->category_id;
        $isActive = $filters->active;

        $query = Product::query()
            ->when($search !== null, function (Builder $query) use ($search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== null, function (Builder $query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->when($isActive !== null, function (Builder $query) use ($isActive): void {
                $query->where('is_active', $isActive);
            });

        $query
            ->orderBy($filters->sort_field, $filters->sort_direction)
            ->orderBy('id');

        return $query;
    }
}
