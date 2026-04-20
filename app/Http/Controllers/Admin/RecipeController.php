<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use App\Services\RecipeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    public function __construct(private readonly RecipeService $service)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $filters = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'search' => ['nullable', 'string', 'max:160'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'in:0,1'],
            'recipe_presence' => ['nullable', 'in:all,with_recipe,without_recipe'],
            'has_low_stock_ingredient' => ['nullable', 'in:0,1'],
            'sort_field' => ['nullable', 'in:name,recipe_items_count,recipe_steps_count'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $paginator = $this->service->paginateForAdmin($filters);
        $summary = $this->service->buildSummary(collect($paginator->items()));

        $recipes = $paginator->through(fn (Product $product): array => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'name' => $product->name,
                'slug' => $product->slug,
                'is_active' => $product->is_active,
                'recipe_items_count' => $product->recipe_items_count,
                'recipe_steps_count' => $product->recipe_steps_count,
                'low_stock_ingredients_count' => $product->low_stock_ingredients_count,
                'has_recipe' => $product->recipe_items_count > 0,
                'product_ingredients' => $product->productIngredients
                    ->map(fn (ProductIngredient $item): array => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'ingredient_id' => $item->ingredient_id,
                        'ingredient_name' => $item->ingredient?->name,
                        'ingredient_unit' => $item->ingredient?->unit,
                        'ingredient_is_active' => $item->ingredient?->is_active ?? false,
                        'ingredient_is_low_stock' => $item->ingredient?->isLowStock() ?? false,
                        'quantity' => (float) $item->quantity,
                        'sort_order' => $item->sort_order,
                        'notes' => $item->notes,
                    ])
                    ->values()
                    ->all(),
                'recipe_steps' => $product->recipeSteps
                    ->map(fn (RecipeStep $step): array => [
                        'id' => $step->id,
                        'product_id' => $step->product_id,
                        'title' => $step->title,
                        'step_type' => $step->step_type,
                        'description' => $step->description,
                        'duration_minutes' => $step->duration_minutes,
                        'wait_minutes' => $step->wait_minutes,
                        'temperature_celsius' => $step->temperature_celsius !== null ? (float) $step->temperature_celsius : null,
                        'sort_order' => $step->sort_order,
                        'is_active' => $step->is_active,
                    ])
                    ->values()
                    ->all(),
                'recipe_summary' => $this->service->buildRecipeWorkflowSummary($product),
            ]);

        return Inertia::render('Admin/Recipes/Index', [
            'recipes' => $recipes,
            'categories' => $this->service->listSelectableCategories(),
            'ingredients' => $this->service->listSelectableIngredients(),
            'stepTypes' => $this->service->listSelectableStepTypes(),
            'filters' => [
                'product_id' => $filters['product_id'] ?? null,
                'search' => (string) ($filters['search'] ?? ''),
                'category_id' => $filters['category_id'] ?? null,
                'is_active' => isset($filters['is_active']) ? (string) $filters['is_active'] : '',
                'recipe_presence' => (string) ($filters['recipe_presence'] ?? 'all'),
                'has_low_stock_ingredient' => isset($filters['has_low_stock_ingredient']) ? (string) $filters['has_low_stock_ingredient'] : '',
                'sort_field' => (string) ($filters['sort_field'] ?? 'name'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'asc'),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
            'summary' => $summary,
        ]);
    }
}
