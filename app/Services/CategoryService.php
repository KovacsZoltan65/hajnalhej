<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(private readonly CategoryRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
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

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Category
    {
        $normalized = $this->normalizePayload($payload);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug']);

        return $this->repository->create($normalized);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Category $category, array $payload): Category
    {
        $normalized = $this->normalizePayload($payload, $category);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug'], $category->id);

        return $this->repository->update($category, $normalized);
    }

    public function delete(Category $category): void
    {
        $this->repository->delete($category);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload, ?Category $category = null): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $slugInput = trim((string) ($payload['slug'] ?? ''));

        if ($slugInput === '') {
            $slugInput = Str::slug($name);
        }

        if ($slugInput === '') {
            $slugInput = $category?->slug ?? 'category';
        }

        return [
            'name' => $name,
            'slug' => $slugInput,
            'description' => $payload['description'] ?? null,
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
        ];
    }

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
