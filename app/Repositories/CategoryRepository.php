<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use App\Services\CacheService;
use App\Traits\Functions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use JsonException;

class CategoryRepository
{
    use Functions;

    protected $tag = 'category';

    public function __construct(private readonly CacheService $cacheService)
    {
    }

    /**
     * Admin oldalon megjelenítendő adatokat szolgáltatja
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->adminQuery($filters)
            ->withCount('products')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * A kiválasztható (is_active = 1) kategóriák listája
     * @return Collection<int, array{id:int,name:string}>
     */
    public function listSelectable(): Collection
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
     * Új kategória elkészítése
     * @param array<string, mixed> $data
     * @return Category
     */
    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    /**
     * Summary of update
     * @param Category $category
     * @param array $data
     * @return Category
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }

    /**
     * Kategória törlése
     * @param Category $category
     * @return void
     */
    public function delete(Category $category): void
    {
        $category->delete();
    }

    /**
     * A slug létezésétnek vizsgálata
     * @param string $slug
     * @param int $ignoreId
     * @return bool
     */
    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Category::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * @param array<string, mixed> $filters
     * @return Builder
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $sortField = (string) ($filters['sort_field'] ?? 'sort_order');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'asc');

        $sortableFields = ['name', 'sort_order', 'is_active'];

        if (! \in_array($sortField, $sortableFields, true)) {
            $sortField = 'sort_order';
        }

        if (! \in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $query = Category::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            });

        $query
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id');

        return $query;
    }
}
