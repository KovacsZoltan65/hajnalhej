<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductionPlan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductionPlanRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->adminQuery($filters)
            ->with([
                'items:id,production_plan_id,product_id,product_name_snapshot,product_slug_snapshot,target_quantity,unit_label,sort_order,computed_ingredient_count,computed_step_count,computed_active_minutes,computed_wait_minutes',
                'items.product:id,name,slug',
                'steps:id,production_plan_id,production_plan_item_id,product_id,depends_on_product_id,title,step_type,description,work_instruction,completion_criteria,attention_points,required_tools,expected_result,starts_at,ends_at,duration_minutes,wait_minutes,sort_order,is_dependency',
            ])
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): ProductionPlan
    {
        return ProductionPlan::query()->create($data);
    }

    /**
     * Summary of update
     * @param ProductionPlan $productionPlan
     * @param array $data
     * @return ProductionPlan
     */
    public function update(ProductionPlan $productionPlan, array $data): ProductionPlan
    {
        $productionPlan->update($data);

        return $productionPlan->refresh();
    }

    /**
     * Summary of delete
     * @param ProductionPlan $productionPlan
     * @return void
     */
    public function delete(ProductionPlan $productionPlan): void
    {
        $productionPlan->delete();
    }

    /**
     * Summary of loadForEditor
     * @param ProductionPlan $productionPlan
     * @return ProductionPlan
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
     * @return Collection<int, array{id:int,name:string,slug:string}>
     */
    public function listSelectableProducts(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
            ]);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = (string) ($filters['status'] ?? '');
        $targetFrom = $filters['target_from'] ?? null;
        $targetTo = $filters['target_to'] ?? null;
        $sortField = (string) ($filters['sort_field'] ?? 'target_at');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'asc');

        $sortableFields = [
            'plan_number',
            'target_at',
            'status',
            'total_active_minutes',
            'total_wait_minutes',
            'total_recipe_minutes',
            'planned_start_at',
            'created_at',
        ];

        if (! \in_array($sortField, $sortableFields, true)) {
            $sortField = 'target_at';
        }

        if (! \in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $query = ProductionPlan::query()
            ->when($search !== '', function (Builder $builder) use ($search): void {
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
            ->when($status !== '', function (Builder $builder) use ($status): void {
                $builder->where('status', $status);
            })
            ->when($targetFrom, function (Builder $builder) use ($targetFrom): void {
                $builder->whereDate('target_at', '>=', $targetFrom);
            })
            ->when($targetTo, function (Builder $builder) use ($targetTo): void {
                $builder->whereDate('target_at', '<=', $targetTo);
            });

        $query->withCount('items');

        $query
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id');

        return $query;
    }
}
