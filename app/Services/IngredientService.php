<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Repositories\IngredientRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IngredientService
{
    public function __construct(private readonly IngredientRepository $repository)
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
     * @return Collection<int, array{id:int,name:string,unit:string,is_low_stock:bool}>
     */
    public function listSelectableActive(): Collection
    {
        return $this->repository->listSelectableActive();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Ingredient
    {
        $normalized = $this->normalizePayload($payload);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug']);

        return $this->repository->create($normalized);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Ingredient $ingredient, array $payload): Ingredient
    {
        $normalized = $this->normalizePayload($payload, $ingredient);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug'], $ingredient->id);

        return $this->repository->update($ingredient, $normalized);
    }

    public function delete(Ingredient $ingredient): void
    {
        $this->repository->delete($ingredient);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload, ?Ingredient $ingredient = null): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $slugInput = trim((string) ($payload['slug'] ?? ''));

        if ($slugInput === '') {
            $slugInput = Str::slug($name);
        }

        if ($slugInput === '') {
            $slugInput = $ingredient?->slug ?? 'ingredient';
        }

        $sku = trim((string) ($payload['sku'] ?? ''));

        return [
            'name' => $name,
            'slug' => $slugInput,
            'sku' => $sku !== '' ? $sku : null,
            'unit' => (string) ($payload['unit'] ?? 'db'),
            'current_stock' => number_format((float) ($payload['current_stock'] ?? 0), 3, '.', ''),
            'minimum_stock' => number_format((float) ($payload['minimum_stock'] ?? 0), 3, '.', ''),
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'notes' => $payload['notes'] ?? null,
        ];
    }

    private function resolveUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($baseSlug);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'ingredient';

        $slug = $baseSlug;
        $counter = 2;

        while ($this->repository->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
