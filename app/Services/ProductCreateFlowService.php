<?php

namespace App\Services;

use App\Data\Products\ProductStoreData;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductCreateFlowService
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ProductIngredientService $ingredientService,
        private readonly RecipeStepService $recipeStepService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function store(array $payload): Product
    {
        return DB::transaction(function () use ($payload): Product {
            $product = $this->productService->store(ProductStoreData::from($payload['product']));

            foreach ((array) ($payload['ingredients'] ?? []) as $ingredient) {
                $this->ingredientService->create($product, $ingredient);
            }

            foreach ((array) ($payload['recipe_steps'] ?? []) as $step) {
                $this->recipeStepService->create($product, $step);
            }

            return $product->refresh()->load(['category', 'productIngredients.ingredient', 'recipeSteps']);
        });
    }
}
