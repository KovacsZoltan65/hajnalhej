<?php

namespace App\Services;

use App\Data\Ingredients\IngredientIndexData;
use App\Data\Ingredients\IngredientInlineUpdateData;
use App\Data\Ingredients\IngredientStoreData;
use App\Data\Ingredients\IngredientUpdateData;
use App\Models\Ingredient;
use App\Repositories\IngredientRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IngredientService
{
    public function __construct(private readonly IngredientRepository $repository) {}

    public function paginateForAdmin(IngredientIndexData $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    /**
     * @return Collection<int, array{id:int,name:string,unit:string,current_stock:float,minimum_stock:float,is_low_stock:bool}>
     */
    public function listSelectableActive(): Collection
    {
        return $this->repository->listSelectableActive();
    }

    public function create(IngredientStoreData $payload): Ingredient
    {
        $normalized = $payload->toPayload();
        $normalized['slug'] = $normalized['slug'] !== '' ? $normalized['slug'] : Str::slug((string) $normalized['name']);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug']);

        return $this->repository->create($normalized);
    }

    public function update(Ingredient $ingredient, IngredientUpdateData $payload): Ingredient
    {
        $normalized = $payload->toPayload();
        $normalized['slug'] = $normalized['slug'] !== '' ? $normalized['slug'] : $ingredient->slug;
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug'], $ingredient->id);

        return $this->repository->update($ingredient, $normalized);
    }

    public function updateInline(Ingredient $ingredient, IngredientInlineUpdateData $payload): Ingredient
    {
        $normalized = match ($payload->field) {
            'current_stock' => ['current_stock' => number_format((float) $payload->value, 3, '.', '')],
            'minimum_stock' => ['minimum_stock' => number_format((float) $payload->value, 3, '.', '')],
            'unit' => ['unit' => (string) $payload->value],
            default => [],
        };

        return $this->repository->update($ingredient, $normalized);
    }

    /**
     * Summary of delete
     */
    public function delete(Ingredient $ingredient): void
    {
        $this->repository->delete($ingredient);
    }

    /**
     * Summary of resolveUniqueSlug
     *
     * @param  mixed  $ignoreId
     */
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
