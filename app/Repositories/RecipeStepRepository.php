<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\RecipeStep;
use Illuminate\Support\Collection;

class RecipeStepRepository
{
    /**
     * @return Collection<int, RecipeStep>
     */
    public function listByProduct(Product $product): Collection
    {
        return $product->recipeSteps()
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(Product $product, array $data): RecipeStep
    {
        return $product->recipeSteps()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(RecipeStep $recipeStep, array $data): RecipeStep
    {
        $recipeStep->update($data);

        return $recipeStep->refresh();
    }

    public function delete(RecipeStep $recipeStep): void
    {
        $recipeStep->delete();
    }
}

