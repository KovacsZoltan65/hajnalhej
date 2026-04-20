<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanItem;
use App\Models\ProductionPlanStep;
use App\Models\RecipeStep;
use App\Repositories\ProductionPlanRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductionPlanService
{
    public function __construct(private readonly ProductionPlanRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    /**
     * @return Collection<int, array{id:int,name:string,slug:string}>
     */
    public function listSelectableProducts(): Collection
    {
        return $this->repository->listSelectableProducts();
    }

    /**
     * @return Collection<int, array{value:string,label:string}>
     */
    public function listStatuses(): Collection
    {
        return collect(ProductionPlan::statuses())
            ->map(fn (string $status): array => [
                'value' => $status,
                'label' => Str::headline($status),
            ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, int $userId): ProductionPlan
    {
        /** @var ProductionPlan $productionPlan */
        $productionPlan = DB::transaction(function () use ($data, $userId): ProductionPlan {
            $targetAt = Carbon::parse((string) ($data['target_ready_at'] ?? $data['target_at']));

            $plan = $this->repository->create([
                'plan_number' => $this->buildPlanNumber(),
                'target_at' => $targetAt,
                'status' => $data['status'] ?? ProductionPlan::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'is_locked' => false,
                'created_by' => $userId,
            ]);

            $this->syncItems($plan, $data['items'] ?? []);
            $this->recalculate($plan);

            return $plan;
        });

        return $this->repository->loadForEditor($productionPlan);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(ProductionPlan $productionPlan, array $data): ProductionPlan
    {
        /** @var ProductionPlan $updated */
        $updated = DB::transaction(function () use ($productionPlan, $data): ProductionPlan {
            $plan = $this->repository->update($productionPlan, [
                'target_at' => Carbon::parse((string) ($data['target_ready_at'] ?? $data['target_at'])),
                'status' => (string) ($data['status'] ?? $productionPlan->status),
                'notes' => $data['notes'] ?? null,
                'is_locked' => (bool) ($data['is_locked'] ?? false),
            ]);

            $this->syncItems($plan, $data['items'] ?? []);
            $this->recalculate($plan);

            return $plan;
        });

        return $this->repository->loadForEditor($updated);
    }

    public function delete(ProductionPlan $productionPlan): void
    {
        $this->repository->delete($productionPlan);
    }

    public function buildPlanPayload(ProductionPlan $productionPlan): array
    {
        $plan = $this->repository->loadForEditor($productionPlan);
        $detailed = $this->loadProductsForItems($plan, $plan->items);
        $requirements = $this->buildIngredientRequirements($detailed);
        $summary = $this->buildSummary($plan, $detailed);

        return [
            'id' => $plan->id,
            'plan_number' => $plan->plan_number,
            'target_at' => $plan->target_at?->toDateTimeString(),
            'target_ready_at' => $plan->target_at?->toDateTimeString(),
            'status' => $plan->status,
            'is_locked' => $plan->is_locked,
            'notes' => $plan->notes,
            'total_active_minutes' => $plan->total_active_minutes,
            'total_wait_minutes' => $plan->total_wait_minutes,
            'total_recipe_minutes' => $plan->total_recipe_minutes,
            'planned_start_at' => $plan->planned_start_at?->toDateTimeString(),
            'items_count' => $plan->items->count(),
            'timeline_steps_count' => $plan->steps->count(),
            'items' => $detailed
                ->map(fn (array $item): array => [
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_slug' => $item['product']->slug,
                    'target_quantity' => $item['target_quantity'],
                    'unit_label' => $item['unit_label'],
                    'sort_order' => $item['sort_order'],
                    'ingredient_count' => $item['ingredient_count'],
                    'step_count' => $item['step_count'],
                    'total_active_minutes' => $item['active_minutes'],
                    'total_wait_minutes' => $item['wait_minutes'],
                    'total_recipe_minutes' => $item['active_minutes'] + $item['wait_minutes'],
                    'suggested_start_at' => $item['suggested_start_at'],
                ])
                ->values()
                ->all(),
            'timeline_steps' => $plan->steps
                ->map(fn (ProductionPlanStep $step): array => [
                    'id' => $step->id,
                    'title' => $step->title,
                    'step_type' => $step->step_type,
                    'description' => $step->description,
                    'work_instruction' => $step->work_instruction,
                    'completion_criteria' => $step->completion_criteria,
                    'attention_points' => $step->attention_points,
                    'required_tools' => $step->required_tools,
                    'expected_result' => $step->expected_result,
                    'starts_at' => $step->starts_at?->toDateTimeString(),
                    'ends_at' => $step->ends_at?->toDateTimeString(),
                    'duration_minutes' => $step->duration_minutes,
                    'wait_minutes' => $step->wait_minutes,
                    'sort_order' => $step->sort_order,
                    'timeline_group' => $step->timeline_group,
                    'is_dependency' => $step->is_dependency,
                    'product_name' => $step->product?->name,
                    'depends_on_product_name' => $step->dependsOnProduct?->name,
                ])
                ->values()
                ->all(),
            'ingredient_requirements' => $requirements,
            'summary' => $summary,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function buildIndexSummary(array $filters = []): array
    {
        $plans = collect($this->paginateForAdmin(array_merge($filters, ['per_page' => 50]))->items());

        return [
            'total_plans' => $plans->count(),
            'ready_plans' => $plans->where('status', ProductionPlan::STATUS_READY)->count(),
            'draft_plans' => $plans->where('status', ProductionPlan::STATUS_DRAFT)->count(),
            'total_recipe_minutes' => (int) $plans->sum('total_recipe_minutes'),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function syncItems(ProductionPlan $productionPlan, array $items): void
    {
        $productIds = collect($items)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $products = Product::query()
            ->whereIn('id', $productIds->all())
            ->get(['id', 'name', 'slug'])
            ->keyBy('id');

        $productionPlan->items()->delete();

        foreach ($items as $index => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $product = $products->get($productId);

            if (! $product instanceof Product) {
                continue;
            }

            $productionPlan->items()->create([
                'product_id' => $productId,
                'product_name_snapshot' => $product->name,
                'product_slug_snapshot' => $product->slug,
                'target_quantity' => (float) ($item['target_quantity'] ?? 0),
                'unit_label' => $item['unit_label'] ?: 'db',
                'sort_order' => (int) ($item['sort_order'] ?? $index),
            ]);
        }
    }

    private function recalculate(ProductionPlan $productionPlan): void
    {
        $plan = $productionPlan->load([
            'items:id,production_plan_id,product_id,target_quantity,sort_order',
            'items.product:id,name,slug',
            'items.product.productIngredients:id,product_id,ingredient_id,quantity',
            'items.product.recipeSteps:id,product_id,duration_minutes,wait_minutes,is_active,sort_order',
        ]);

        $totalActive = 0;
        $totalWait = 0;

        /** @var ProductionPlanItem $item */
        foreach ($plan->items as $item) {
            $product = $item->product;

            if (! $product instanceof Product) {
                continue;
            }

            $activePerUnit = (int) $product->recipeSteps
                ->where('is_active', true)
                ->sum(fn (RecipeStep $step): int => (int) ($step->duration_minutes ?? 0));
            $waitPerUnit = (int) $product->recipeSteps
                ->where('is_active', true)
                ->sum(fn (RecipeStep $step): int => (int) ($step->wait_minutes ?? 0));

            $quantity = (float) $item->target_quantity;
            $active = (int) round($activePerUnit * $quantity);
            $wait = (int) round($waitPerUnit * $quantity);

            $item->update([
                'computed_ingredient_count' => $product->productIngredients->count(),
                'computed_step_count' => $product->recipeSteps->where('is_active', true)->count(),
                'computed_active_minutes' => $active,
                'computed_wait_minutes' => $wait,
            ]);

            $totalActive += $active;
            $totalWait += $wait;
        }

        $targetAt = $plan->target_at instanceof Carbon ? $plan->target_at : Carbon::parse((string) $plan->target_at);
        $totalRecipe = $totalActive + $totalWait;

        $plan->update([
            'total_active_minutes' => $totalActive,
            'total_wait_minutes' => $totalWait,
            'total_recipe_minutes' => $totalRecipe,
            'planned_start_at' => $targetAt->copy()->subMinutes($totalRecipe),
        ]);

        $this->rebuildTimelineSteps($plan);
    }

    private function buildPlanNumber(): string
    {
        $datePart = Carbon::now()->format('Ymd');

        $lastSequence = (int) ProductionPlan::query()
            ->whereDate('created_at', Carbon::today())
            ->count() + 1;

        return sprintf('PLAN-%s-%03d', $datePart, $lastSequence);
    }

    /**
     * @param Collection<int, ProductionPlanItem> $items
     * @return Collection<int, array{product:Product,target_quantity:float,unit_label:string,sort_order:int,ingredient_count:int,step_count:int,active_minutes:int,wait_minutes:int,suggested_start_at:string}>
     */
    private function loadProductsForItems(ProductionPlan $plan, Collection $items): Collection
    {
        $productIds = $items->pluck('product_id')->unique()->values();

        $products = Product::query()
            ->with([
                'productIngredients.ingredient:id,name,slug,unit,current_stock,minimum_stock,is_active',
                'recipeSteps' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->whereIn('id', $productIds->all())
            ->get()
            ->keyBy('id');

        return $items
            ->map(function (ProductionPlanItem $item) use ($products, $plan): ?array {
                $product = $products->get($item->product_id);

                if (! $product instanceof Product) {
                    return null;
                }

                $quantity = (float) $item->target_quantity;
                $active = (int) round((int) $product->recipeSteps->sum('duration_minutes') * $quantity);
                $wait = (int) round((int) $product->recipeSteps->sum('wait_minutes') * $quantity);

                return [
                    'product' => $product,
                    'target_quantity' => $quantity,
                    'unit_label' => $item->unit_label,
                    'sort_order' => $item->sort_order,
                    'ingredient_count' => $product->productIngredients->count(),
                    'step_count' => $product->recipeSteps->count(),
                    'active_minutes' => $active,
                    'wait_minutes' => $wait,
                    'suggested_start_at' => (string) $plan->target_at?->copy()->subMinutes($active + $wait)?->toDateTimeString(),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @param Collection<int, array{product:Product,target_quantity:float,unit_label:string,sort_order:int,ingredient_count:int,step_count:int,active_minutes:int,wait_minutes:int,suggested_start_at:string}> $items
     * @return array<int, array{ingredient_id:int,name:string,unit:string,total_required:float,current_stock:float,minimum_stock:float,shortage:float,is_low_stock:bool}>
     */
    private function buildIngredientRequirements(Collection $items): array
    {
        $aggregated = [];

        foreach ($items as $item) {
            /** @var ProductIngredient $bomItem */
            foreach ($item['product']->productIngredients as $bomItem) {
                $ingredient = $bomItem->ingredient;

                if (! $ingredient instanceof Ingredient) {
                    continue;
                }

                $ingredientId = $ingredient->id;
                $required = (float) $bomItem->quantity * $item['target_quantity'];

                if (! isset($aggregated[$ingredientId])) {
                    $aggregated[$ingredientId] = [
                        'ingredient_id' => $ingredientId,
                        'name' => $ingredient->name,
                        'unit' => $ingredient->unit,
                        'total_required' => 0.0,
                        'current_stock' => (float) $ingredient->current_stock,
                        'minimum_stock' => (float) $ingredient->minimum_stock,
                    ];
                }

                $aggregated[$ingredientId]['total_required'] += $required;
            }
        }

        return collect($aggregated)
            ->map(function (array $row): array {
                $shortage = max(0, $row['total_required'] - $row['current_stock']);

                return [
                    'ingredient_id' => $row['ingredient_id'],
                    'name' => $row['name'],
                    'unit' => $row['unit'],
                    'total_required' => round((float) $row['total_required'], 3),
                    'current_stock' => round((float) $row['current_stock'], 3),
                    'minimum_stock' => round((float) $row['minimum_stock'], 3),
                    'shortage' => round((float) $shortage, 3),
                    'is_low_stock' => ((float) $row['current_stock']) <= ((float) $row['minimum_stock']) || $shortage > 0,
                ];
            })
            ->sortByDesc('shortage')
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, array{product:Product,target_quantity:float,unit_label:string,sort_order:int,ingredient_count:int,step_count:int,active_minutes:int,wait_minutes:int,suggested_start_at:string}> $items
     * @return array{items_count:int,products_count:int,total_active_minutes:int,total_wait_minutes:int,total_recipe_minutes:int,ingredients_count:int,shortage_ingredients_count:int}
     */
    private function buildSummary(ProductionPlan $plan, Collection $items): array
    {
        $requirements = $this->buildIngredientRequirements($items);
        $timelineStartAt = $plan->steps->min('starts_at');
        $timelineEndAt = $plan->steps->max('ends_at');
        $dependencyStepsCount = $plan->steps->where('is_dependency', true)->count();

        return [
            'items_count' => $items->count(),
            'products_count' => $items->pluck('product.id')->unique()->count(),
            'total_active_minutes' => (int) $plan->total_active_minutes,
            'total_wait_minutes' => (int) $plan->total_wait_minutes,
            'total_recipe_minutes' => (int) $plan->total_recipe_minutes,
            'ingredients_count' => count($requirements),
            'shortage_ingredients_count' => collect($requirements)->where('shortage', '>', 0)->count(),
            'timeline_steps_count' => $plan->steps->count(),
            'dependency_steps_count' => $dependencyStepsCount,
            'timeline_start_at' => $timelineStartAt?->toDateTimeString(),
            'timeline_end_at' => $timelineEndAt?->toDateTimeString(),
        ];
    }

    private function rebuildTimelineSteps(ProductionPlan $productionPlan): void
    {
        $plan = $productionPlan->load([
            'items:id,production_plan_id,product_id,target_quantity,sort_order',
            'items.product:id,name,slug',
            'items.product.productIngredients:id,product_id,ingredient_id,quantity',
            'items.product.productIngredients.ingredient:id,name,slug,unit',
            'items.product.recipeSteps:id,product_id,title,step_type,description,work_instruction,completion_criteria,attention_points,required_tools,expected_result,duration_minutes,wait_minutes,sort_order,is_active',
        ]);

        $targetReadyAt = $plan->target_at instanceof Carbon ? $plan->target_at->copy() : Carbon::parse((string) $plan->target_at);
        $rows = [];

        foreach ($plan->items->sortBy('sort_order')->values() as $item) {
            /** @var ProductionPlanItem $item */
            $product = $item->product;

            if (! $product instanceof Product) {
                continue;
            }

            $mainRows = $this->buildTimelineRowsFromRecipe(
                productionPlanId: $plan->id,
                item: $item,
                product: $product,
                deadlineAt: $targetReadyAt->copy(),
                isDependency: false,
                dependsOnProductId: null,
            );

            $rows = [...$rows, ...$mainRows['rows']];

            $itemStartAt = $mainRows['starts_at'] ?? $targetReadyAt->copy();
            $dependencyProducts = $this->resolveDependencyProducts($product);

            foreach ($dependencyProducts as $dependencyProduct) {
                $dependencyRows = $this->buildTimelineRowsFromRecipe(
                    productionPlanId: $plan->id,
                    item: $item,
                    product: $dependencyProduct,
                    deadlineAt: $itemStartAt->copy(),
                    isDependency: true,
                    dependsOnProductId: $product->id,
                );

                $rows = [...$rows, ...$dependencyRows['rows']];
            }
        }

        usort($rows, static function (array $left, array $right): int {
            $leftStart = (string) $left['starts_at'];
            $rightStart = (string) $right['starts_at'];

            if ($leftStart === $rightStart) {
                return ($left['is_dependency'] <=> $right['is_dependency']);
            }

            return $leftStart <=> $rightStart;
        });

        foreach ($rows as $index => &$row) {
            $row['sort_order'] = $index;
        }
        unset($row);

        $plan->steps()->delete();
        $plan->steps()->createMany($rows);

        $earliestStart = collect($rows)->min('starts_at');

        if ($earliestStart === null) {
            return;
        }

        $plan->update([
            'planned_start_at' => Carbon::parse((string) $earliestStart),
        ]);
    }

    /**
     * @return array{rows:array<int, array<string, mixed>>, starts_at:Carbon|null}
     */
    private function buildTimelineRowsFromRecipe(
        int $productionPlanId,
        ProductionPlanItem $item,
        Product $product,
        Carbon $deadlineAt,
        bool $isDependency,
        ?int $dependsOnProductId,
    ): array {
        $steps = $product->recipeSteps
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->values();

        if ($steps->isEmpty()) {
            return ['rows' => [], 'starts_at' => null];
        }

        $cursor = $deadlineAt->copy();
        $rows = [];
        $quantityFactor = max(1, (int) ceil((float) $item->target_quantity));

        foreach ($steps->reverse()->values() as $reverseIndex => $step) {
            /** @var RecipeStep $step */
            $duration = (int) (($step->duration_minutes ?? 0) * $quantityFactor);
            $wait = (int) (($step->wait_minutes ?? 0) * $quantityFactor);
            $total = max(1, $duration + $wait);

            $startsAt = $cursor->copy()->subMinutes($total);
            $isStarterFeed = $isDependency && Str::contains(Str::lower($step->title), ['etet', 'feed', 'frissit']);
            $titlePrefix = $isStarterFeed ? 'Kovasz etetes' : ($isDependency ? 'Starter' : $product->name);

            $rows[] = [
                'production_plan_id' => $productionPlanId,
                'production_plan_item_id' => $item->id,
                'product_id' => $product->id,
                'depends_on_product_id' => $dependsOnProductId,
                'title' => sprintf('%s - %s', $titlePrefix, $step->title),
                'step_type' => $step->step_type,
                'description' => $step->description,
                'work_instruction' => $step->work_instruction,
                'completion_criteria' => $step->completion_criteria,
                'attention_points' => $step->attention_points,
                'required_tools' => $step->required_tools,
                'expected_result' => $step->expected_result,
                'starts_at' => $startsAt->toDateTimeString(),
                'ends_at' => $cursor->toDateTimeString(),
                'duration_minutes' => $duration,
                'wait_minutes' => $wait,
                'sort_order' => $reverseIndex,
                'timeline_group' => $isDependency ? sprintf('starter:%s', $product->slug) : sprintf('product:%s', $product->slug),
                'is_dependency' => $isDependency,
                'meta' => [
                    'quantity_factor' => $quantityFactor,
                    'source_step_id' => $step->id,
                    'source_product_slug' => $product->slug,
                ],
            ];

            $cursor = $startsAt;
        }

        return [
            'rows' => array_reverse($rows),
            'starts_at' => $cursor,
        ];
    }

    /**
     * @return Collection<int, Product>
     */
    private function resolveDependencyProducts(Product $product): Collection
    {
        $ingredients = $product->productIngredients
            ->map(fn (ProductIngredient $item) => $item->ingredient)
            ->filter()
            ->values();

        if ($ingredients->isEmpty()) {
            return collect();
        }

        $slugCandidates = $ingredients
            ->map(fn (Ingredient $ingredient): string => (string) $ingredient->slug)
            ->filter()
            ->unique()
            ->values();
        $kovaszIngredientDetected = $ingredients
            ->contains(fn (Ingredient $ingredient): bool => Str::contains(Str::lower("{$ingredient->slug} {$ingredient->name}"), 'kovasz'));

        $candidateProducts = Product::query()
            ->with([
                'recipeSteps' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->whereKeyNot($product->id)
            ->get();

        $resolved = $candidateProducts
            ->filter(fn (Product $candidate): bool => $slugCandidates->contains($candidate->slug))
            ->values();

        if ($kovaszIngredientDetected && $resolved->isEmpty()) {
            $resolved = $candidateProducts
                ->filter(function (Product $candidate): bool {
                    $haystack = Str::lower("{$candidate->slug} {$candidate->name}");

                    if (! Str::contains($haystack, 'kovasz')) {
                        return false;
                    }

                    return $candidate->recipeSteps->isNotEmpty();
                })
                ->sortBy(fn (Product $candidate): int => ((float) $candidate->price <= 0 ? 0 : 1))
                ->take(1)
                ->values();
        }

        return $resolved;
    }
}
