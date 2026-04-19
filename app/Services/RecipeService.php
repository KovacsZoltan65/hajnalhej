<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductIngredient;
use App\Repositories\RecipeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RecipeService
{
    public function __construct(
        private readonly RecipeRepository $repository,
        private readonly ProductIngredientService $productIngredientService,
    ) {}

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
    public function listSelectableCategories(): Collection
    {
        return $this->repository->listSelectableCategories();
    }

    /**
     * @return Collection<int, array{id:int,name:string,unit:string,is_low_stock:bool}>
     */
    public function listSelectableIngredients(): Collection
    {
        return $this->productIngredientService->listSelectableIngredients();
    }

    /**
     * @param Collection<int, Product> $products
     * @return array<string, int>
     */
    public function buildSummary(Collection $products): array
    {
        $total = $products->count();
        $withRecipe = $products->filter(fn (Product $product): bool => $product->recipe_items_count > 0)->count();
        $withLowStock = $products->filter(fn (Product $product): bool => $product->low_stock_ingredients_count > 0)->count();

        return [
            'total_products' => $total,
            'with_recipe' => $withRecipe,
            'without_recipe' => max(0, $total - $withRecipe),
            'with_low_stock' => $withLowStock,
        ];
    }
}
