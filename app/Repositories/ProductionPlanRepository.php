<?php

namespace App\Repositories;

use App\Data\ProductionPlans\ProductionPlanIndexData;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ProductionPlan;
use App\Models\RecipeStep;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductionPlanRepository
{
    public function paginateForAdmin(ProductionPlanIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->with([
                'items:id,production_plan_id,product_id,product_name_snapshot,product_slug_snapshot,target_quantity,unit_label,sort_order,computed_ingredient_count,computed_step_count,computed_active_minutes,computed_wait_minutes',
                'items.product:id,name,slug',
                'steps:id,production_plan_id,production_plan_item_id,product_id,depends_on_product_id,title,step_type,description,work_instruction,completion_criteria,attention_points,required_tools,expected_result,starts_at,ends_at,duration_minutes,wait_minutes,sort_order,is_dependency',
            ])
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ProductionPlan
    {
        return ProductionPlan::query()->create($data);
    }

    /**
     * Summary of update
     */
    public function update(ProductionPlan $productionPlan, array $data): ProductionPlan
    {
        $productionPlan->update($data);

        return $productionPlan->refresh();
    }

    /**
     * Summary of delete
     */
    public function delete(ProductionPlan $productionPlan): void
    {
        $productionPlan->delete();
    }

    /**
     * Summary of loadForEditor
     */
    public function loadForEditor(ProductionPlan $productionPlan): ProductionPlan
    {
        return $productionPlan->load([
            'items' => fn ($query) => $query
                ->select([
                    'id',
                    'production_plan_id',
                    'product_id',
                    'product_name_snapshot',
                    'product_slug_snapshot',
                    'target_quantity',
                    'unit_label',
                    'sort_order',
                    'computed_ingredient_count',
                    'computed_step_count',
                    'computed_active_minutes',
                    'computed_wait_minutes',
                ])
                ->orderBy('sort_order')
                ->orderBy('id'),
            'items.product:id,name,slug',
            'steps:id,production_plan_id,production_plan_item_id,product_id,depends_on_product_id,title,step_type,description,work_instruction,completion_criteria,attention_points,required_tools,expected_result,starts_at,ends_at,duration_minutes,wait_minutes,sort_order,timeline_group,is_dependency,meta',
            'steps.product:id,name,slug',
            'steps.dependsOnProduct:id,name,slug',
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listActiveProductsForCreateFlow(): Collection
    {
        return Product::query()
            ->with([
                'category:id,name',
                'productIngredients.ingredient:id,name,slug,unit,current_stock,minimum_stock,is_active',
                'recipeSteps' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category_name' => $product->category?->name,
                'unit_label' => 'db',
                'product_ingredients' => $product->productIngredients
                    ->map(fn (ProductIngredient $item): array => [
                        'ingredient_id' => $item->ingredient_id,
                        'ingredient_name' => $item->ingredient?->name,
                        'ingredient_unit' => $item->ingredient?->unit,
                        'quantity' => (float) $item->quantity,
                        'current_stock' => (float) ($item->ingredient?->current_stock ?? 0),
                        'minimum_stock' => (float) ($item->ingredient?->minimum_stock ?? 0),
                    ])
                    ->values()
                    ->all(),
                'recipe_steps' => $product->recipeSteps
                    ->map(fn (RecipeStep $step): array => [
                        'id' => $step->id,
                        'title' => $step->title,
                        'step_type' => $step->step_type,
                        'work_instruction' => $step->work_instruction,
                        'duration_minutes' => (int) ($step->duration_minutes ?? 0),
                        'wait_minutes' => (int) ($step->wait_minutes ?? 0),
                        'sort_order' => $step->sort_order,
                    ])
                    ->values()
                    ->all(),
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listSelectableProducts(): Collection
    {
        return Product::query()
            ->with([
                'productIngredients.ingredient:id,name,slug,unit,current_stock,minimum_stock,is_active',
                'recipeSteps' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'product_ingredients' => $product->productIngredients
                    ->map(fn (ProductIngredient $item): array => [
                        'ingredient_id' => $item->ingredient_id,
                        'ingredient_name' => $item->ingredient?->name,
                        'ingredient_unit' => $item->ingredient?->unit,
                        'quantity' => (float) $item->quantity,
                        'current_stock' => (float) ($item->ingredient?->current_stock ?? 0),
                        'minimum_stock' => (float) ($item->ingredient?->minimum_stock ?? 0),
                    ])
                    ->values()
                    ->all(),
                'recipe_steps' => $product->recipeSteps
                    ->map(fn (RecipeStep $step): array => [
                        'id' => $step->id,
                        'duration_minutes' => (int) ($step->duration_minutes ?? 0),
                        'wait_minutes' => (int) ($step->wait_minutes ?? 0),
                    ])
                    ->values()
                    ->all(),
            ]);
    }

    private function adminQuery(ProductionPlanIndexData $filters): Builder
    {
        $query = ProductionPlan::query()
            ->when($filters->search !== null, function (Builder $builder) use ($filters): void {
                $search = (string) $filters->search;

                $builder->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('plan_number', 'like', "%{$search}%")
                        ->orWhereHas('items', function (Builder $itemQuery) use ($search): void {
                            $itemQuery
                                ->where('product_name_snapshot', 'like', "%{$search}%")
                                ->orWhere('product_slug_snapshot', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters->status !== null, function (Builder $builder) use ($filters): void {
                $builder->where('status', $filters->status);
            })
            ->when($filters->target_from !== null, function (Builder $builder) use ($filters): void {
                $builder->whereDate('target_at', '>=', $filters->target_from);
            })
            ->when($filters->target_to !== null, function (Builder $builder) use ($filters): void {
                $builder->whereDate('target_at', '<=', $filters->target_to);
            });

        $query->withCount('items');

        $query
            ->orderBy($filters->sort_field, $filters->sort_direction)
            ->orderBy('id');

        return $query;
    }
}
