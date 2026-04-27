<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductionPlanRequest;
use App\Http\Requests\UpdateProductionPlanRequest;
use App\Models\ProductionPlan;
use App\Services\ProductionPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductionPlanController extends Controller
{
    /**
     * @param ProductionPlanService $service
     */
    public function __construct(private readonly ProductionPlanService $service)
    {
    }

    /**
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ProductionPlan::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:180'],
            'status' => ['nullable', 'string', 'in:draft,calculated,ready,archived'],
            'target_from' => ['nullable', 'date'],
            'target_to' => ['nullable', 'date'],
            'sort_field' => ['nullable', 'in:plan_number,target_at,status,total_active_minutes,total_wait_minutes,total_recipe_minutes,planned_start_at,created_at'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $paginator = $this->service->paginateForAdmin($filters);

        $plans = $paginator->through(fn (ProductionPlan $plan): array => [
            'id' => $plan->id,
            'plan_number' => $plan->plan_number,
            'target_at' => $plan->target_at?->toDateTimeString(),
            'target_ready_at' => $plan->target_at?->toDateTimeString(),
            'status' => $plan->status,
            'is_locked' => $plan->is_locked,
            'total_active_minutes' => $plan->total_active_minutes,
            'total_wait_minutes' => $plan->total_wait_minutes,
            'total_recipe_minutes' => $plan->total_recipe_minutes,
            'planned_start_at' => $plan->planned_start_at?->toDateTimeString(),
            'items_count' => (int) ($plan->items_count ?? $plan->items->count()),
            'items' => $plan->items
                ->map(fn ($item): array => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name_snapshot,
                    'product_slug' => $item->product_slug_snapshot,
                    'target_quantity' => (float) $item->target_quantity,
                    'unit_label' => $item->unit_label,
                    'sort_order' => $item->sort_order,
                ])
                ->values()
                ->all(),
            'details' => $this->service->buildPlanPayload($plan),
        ]);

        return Inertia::render('Admin/ProductionPlans/Index', [
            'productionPlans' => $plans,
            'products' => $this->service->listSelectableProducts(),
            'statuses' => $this->service->listStatuses(),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'status' => (string) ($filters['status'] ?? ''),
                'target_from' => $filters['target_from'] ?? null,
                'target_to' => $filters['target_to'] ?? null,
                'sort_field' => (string) ($filters['sort_field'] ?? 'target_at'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'asc'),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
            'summary' => $this->service->buildIndexSummary($filters),
        ]);
    }

    /**
     * @param StoreProductionPlanRequest $request
     * @return RedirectResponse
     */
    public function store(StoreProductionPlanRequest $request): RedirectResponse
    {
        $this->authorize('create', ProductionPlan::class);

        $this->service->create($request->validated(), (int) $request->user()->id);

        return redirect()
            ->route('admin.production-plans.index')
            ->with('success', 'Gyártási terv létrehozva.');
    }

    /**
     * @param UpdateProductionPlanRequest $request
     * @param ProductionPlan $productionPlan
     * @return RedirectResponse
     */
    public function update(UpdateProductionPlanRequest $request, ProductionPlan $productionPlan): RedirectResponse
    {
        $this->authorize('update', $productionPlan);

        $this->service->update($productionPlan, $request->validated());

        return redirect()
            ->route('admin.production-plans.index')
            ->with('success', 'Gyártási terv frissítve.');
    }

    /**
     * @param Request $request
     * @param ProductionPlan $productionPlan
     * @return RedirectResponse
     */
    public function destroy(Request $request, ProductionPlan $productionPlan): RedirectResponse
    {
        $this->authorize('delete', $productionPlan);

        $this->service->delete($productionPlan);

        return redirect()
            ->route('admin.production-plans.index')
            ->with('success', 'Gyártási terv törölve.');
    }
}


