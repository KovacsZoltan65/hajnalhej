<?php

namespace App\Repositories;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductIngredientRepository
{
    /**
     * @return Collection<int, array{id:int,name:string,unit:string,is_low_stock:bool}>
     */
    public function listSelectableIngredients(): Collection
    {
        return Ingredient::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'current_stock', 'minimum_stock'])
            ->map(fn (Ingredient $ingredient): array => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'is_low_stock' => $ingredient->isLowStock(),
            ]);
    }

    /**
     * @return Collection<int, ProductIngredient>
     */
    public function listByProduct(Product $product): Collection
    {
        return $product->productIngredients()
            ->with('ingredient')
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(Product $product, array $data): ProductIngredient
    {
        return $product->productIngredients()->create($data)->load('ingredient');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(ProductIngredient $productIngredient, array $data): ProductIngredient
    {
        $productIngredient->update($data);

        return $productIngredient->refresh()->load('ingredient');
    }

    public function delete(ProductIngredient $productIngredient): void
    {
        $productIngredient->delete();
    }

    public function existsForProduct(Product $product, int $ingredientId, ?int $ignoreId = null): bool
    {
        return ProductIngredient::query()
            ->where('product_id', $product->id)
            ->where('ingredient_id', $ingredientId)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
