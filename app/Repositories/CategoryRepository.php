<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Categories\CategoryIndexData;
use App\Models\Category;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\Cache\CacheVersionService;
use App\Traits\Functions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    use Functions;

    protected $tag = 'category';

    public function __construct(
        private readonly CacheVersionService $cacheVersionService,
    ) {}

    /**
     * Admin oldalon megjelenítendő adatokat szolgáltatja
     */
    public function paginateForAdmin(CategoryIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->withCount('products')
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    /**
     * A kiválasztható (is_active = 1) kategóriák listája
     *
     * @return Collection<int, array{id:int,name:string}>
     */
    public function listSelectable(): Collection
    {
        $version = $this->cacheVersionService->get(CacheNamespaces::SELECTORS_CATEGORIES);
        $key = CacheKeyService::make(CacheNamespaces::SELECTORS_CATEGORIES, $version, [
            'locale' => app()->getLocale(),
        ]);

        return Cache::remember($key, now()->addMinutes(30), fn (): Collection => Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ]));
    }

    /**
     * Új kategória elkészítése
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }

    /**
     * Kategória törlése
     */
    public function delete(Category $category): void
    {
        $category->delete();
    }

    /**
     * A slug létezésétnek vizsgálata
     */
    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Category::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    private function adminQuery(CategoryIndexData $filters): Builder
    {
        $query = Category::query()
            ->when($filters->search !== null, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $innerQuery) use ($filters): void {
                    $innerQuery
                        ->where('name', 'like', "%{$filters->search}%")
                        ->orWhere('slug', 'like', "%{$filters->search}%");
                });
            });

        $query
            ->orderBy($filters->sort_field, $filters->sort_direction)
            ->orderBy('id');

        return $query;
    }
}
