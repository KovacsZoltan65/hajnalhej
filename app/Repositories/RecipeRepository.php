<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RecipeRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->adminQuery($filters)
            ->with([
                'category:id,name',
                'productIngredients.ingredient:id,name,unit,current_stock,minimum_stock,is_active,deleted_at',
                'recipeSteps:id,product_id,title,step_type,description,duration_minutes,wait_minutes,temperature_celsius,sort_order,is_active',
            ])
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, array{id:int,name:string}>
     */
    public function listSelectableCategories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ]);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $productId = $filters['product_id'] ?? null;
        $categoryId = $filters['category_id'] ?? null;
        $isActive = $filters['is_active'] ?? null;
        $recipePresence = (string) ($filters['recipe_presence'] ?? 'all');
        $hasLowStock = (string) ($filters['has_low_stock_ingredient'] ?? '');
        $sortField = (string) ($filters['sort_field'] ?? 'name');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'asc');

        $sortableFields = ['name', 'recipe_items_count', 'recipe_steps_count'];

        if (! \in_array($sortField, $sortableFields, true)) {
            $sortField = 'name';
        }

        if (! \in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $query = Product::query()
            ->when($productId !== null && $productId !== '', function (Builder $builder) use ($productId): void {
                $builder->whereKey((int) $productId);
            })
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== null && $categoryId !== '', function (Builder $builder) use ($categoryId): void {
                $builder->where('category_id', (int) $categoryId);
            })
            ->when($isActive !== null && $isActive !== '', function (Builder $builder) use ($isActive): void {
                $builder->where('is_active', (bool) $isActive);
            });

        if ($recipePresence === 'with_recipe') {
            $query->has('productIngredients');
        }

        if ($recipePresence === 'without_recipe') {
            $query->doesntHave('productIngredients');
        }

        if ($hasLowStock === '1') {
            $query->whereHas('productIngredients.ingredient', function (Builder $builder): void {
                $builder
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->whereColumn('current_stock', '<=', 'minimum_stock');
            });
        }

        $query->withCount([
            'productIngredients as recipe_items_count',
            'recipeSteps as recipe_steps_count',
            'productIngredients as low_stock_ingredients_count' => function (Builder $builder): void {
                $builder->whereHas('ingredient', function (Builder $ingredientQuery): void {
                    $ingredientQuery
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                        ->whereColumn('current_stock', '<=', 'minimum_stock');
                });
            },
        ]);

        $query
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id');

        return $query;
    }
}
