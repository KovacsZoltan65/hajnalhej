<?php

namespace App\Repositories;

use App\Data\Ingredients\IngredientIndexData;
use App\Models\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IngredientRepository
{
    public function paginateForAdmin(IngredientIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    /**
     * @return Collection<int, array{id:int,name:string,unit:string,current_stock:float,minimum_stock:float,is_low_stock:bool}>
     */
    public function listSelectableActive(): Collection
    {
        return Ingredient::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'current_stock', 'minimum_stock'])
            ->map(fn (Ingredient $ingredient): array => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'estimated_unit_cost' => $ingredient->estimated_unit_cost,
                'current_stock' => (float) $ingredient->current_stock,
                'minimum_stock' => (float) $ingredient->minimum_stock,
                'is_low_stock' => $ingredient->isLowStock(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Ingredient
    {
        return Ingredient::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Ingredient $ingredient, array $data): Ingredient
    {
        $ingredient->update($data);

        return $ingredient->refresh();
    }

    /**
     * Summary of delete
     */
    public function delete(Ingredient $ingredient): void
    {
        $ingredient->delete();
    }

    /**
     * Summary of slugExists
     *
     * @param  mixed  $ignoreId
     */
    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Ingredient::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    private function adminQuery(IngredientIndexData $filters): Builder
    {
        $query = Ingredient::query()
            ->when($filters->search !== null, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $innerQuery) use ($filters): void {
                    $innerQuery
                        ->where('name', 'like', "%{$filters->search}%")
                        ->orWhere('slug', 'like', "%{$filters->search}%")
                        ->orWhere('sku', 'like', "%{$filters->search}%");
                });
            })
            ->when($filters->is_active !== null, function (Builder $query) use ($filters): void {
                $query->where('is_active', $filters->is_active);
            })
            ->when($filters->unit !== null, function (Builder $query) use ($filters): void {
                $query->where('unit', $filters->unit);
            });

        $query
            ->orderBy($filters->sort_field, $filters->sort_direction)
            ->orderBy('id');

        return $query;
    }
}
