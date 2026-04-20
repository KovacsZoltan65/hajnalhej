<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
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
     * @return Collection<int, array{value:string,label:string}>
     */
    public function listSelectableStepTypes(): Collection
    {
        return collect(RecipeStep::stepTypes())
            ->map(fn (string $type): array => [
                'value' => $type,
                'label' => ucfirst($type),
            ]);
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
        $totalIngredients = $products->sum(fn (Product $product): int => (int) ($product->recipe_items_count ?? 0));
        $totalSteps = $products->sum(fn (Product $product): int => (int) ($product->recipe_steps_count ?? 0));
        $totalActiveMinutes = $products->sum(fn (Product $product): int => $this->buildRecipeWorkflowSummary($product)['total_active_minutes']);
        $totalWaitMinutes = $products->sum(fn (Product $product): int => $this->buildRecipeWorkflowSummary($product)['total_wait_minutes']);

        return [
            'total_products' => $total,
            'with_recipe' => $withRecipe,
            'without_recipe' => max(0, $total - $withRecipe),
            'with_low_stock' => $withLowStock,
            'total_ingredients' => $totalIngredients,
            'total_steps' => $totalSteps,
            'total_active_minutes' => $totalActiveMinutes,
            'total_wait_minutes' => $totalWaitMinutes,
            'total_recipe_minutes' => $totalActiveMinutes + $totalWaitMinutes,
        ];
    }

    /**
     * @return array{ingredients_count:int,steps_count:int,total_active_minutes:int,total_wait_minutes:int,total_recipe_minutes:int}
     */
    public function buildRecipeWorkflowSummary(Product $product): array
    {
        $activeMinutes = (int) $product->recipeSteps->sum(fn (RecipeStep $step): int => (int) ($step->duration_minutes ?? 0));
        $waitMinutes = (int) $product->recipeSteps->sum(fn (RecipeStep $step): int => (int) ($step->wait_minutes ?? 0));

        return [
            'ingredients_count' => (int) ($product->recipe_items_count ?? $product->productIngredients->count()),
            'steps_count' => (int) ($product->recipe_steps_count ?? $product->recipeSteps->count()),
            'total_active_minutes' => $activeMinutes,
            'total_wait_minutes' => $waitMinutes,
            'total_recipe_minutes' => $activeMinutes + $waitMinutes,
        ];
    }
}
