<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryRepository
{
    /**
     * @param array<string, mixed> $filters
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
     * @param array<string, mixed> $data
     */
    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Category::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * @param array<string, mixed> $filters
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
