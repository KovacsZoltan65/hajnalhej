<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Categories\CategoryIndexData;
use App\Data\Categories\CategoryStoreData;
use App\Data\Categories\CategoryUpdateData;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\Cache\SelectorCacheInvalidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly SelectorCacheInvalidator $selectorCacheInvalidator,
    ) {}

    public function paginateForAdmin(CategoryIndexData $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    /**
     * @return Collection<int, array{id:int,name:string}>
     */
    public function listSelectable(): Collection
    {
        return $this->repository->listSelectable();
    }

    public function create(CategoryStoreData $payload): Category
    {
        $normalized = $payload->toPayload();
        $normalized['slug'] = $normalized['slug'] !== '' ? $normalized['slug'] : Str::slug((string) $normalized['name']);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug']);

        $category = $this->repository->create($normalized);

        $this->selectorCacheInvalidator->categories();

        return $category;
    }

    public function update(Category $category, CategoryUpdateData $payload): Category
    {
        $normalized = $payload->toPayload();
        $normalized['slug'] = $normalized['slug'] !== '' ? $normalized['slug'] : $category->slug;
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug'], $category->id);

        $category = $this->repository->update($category, $normalized);

        $this->selectorCacheInvalidator->categories();

        return $category;
    }

    /**
     * Summary of delete
     */
    public function delete(Category $category): void
    {
        $this->repository->delete($category);

        $this->selectorCacheInvalidator->categories();
    }

    /**
     * Summary of resolveUniqueSlug
     *
     * @param  mixed  $ignoreId
     */
    private function resolveUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($baseSlug);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'category';

        $slug = $baseSlug;
        $counter = 2;

        while ($this->repository->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
