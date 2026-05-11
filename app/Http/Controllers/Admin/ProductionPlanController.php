<?php

namespace App\Http\Controllers\Admin;

use App\Data\ProductionPlans\ProductionPlanDetailData;
use App\Data\ProductionPlans\ProductionPlanIndexData;
use App\Data\ProductionPlans\ProductionPlanListItemData;
use App\Data\ProductionPlans\ProductionPlanStoreData;
use App\Data\ProductionPlans\ProductionPlanUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductionPlanCreateFlowRequest;
use App\Http\Requests\StoreProductionPlanRequest;
use App\Http\Requests\UpdateProductionPlanRequest;
use App\Models\ProductionPlan;
use App\Services\ProductionPlanCreateFlowService;
use App\Services\ProductionPlanService;
use App\Support\InertiaPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductionPlanController extends Controller
{
    public function __construct(
        private readonly ProductionPlanService $service,
        private readonly ProductionPlanCreateFlowService $createFlowService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ProductionPlan::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:180'],
            'status' => ['nullable', 'string', 'in:draft,calculated,ready,archived'],
            'target_from' => ['nullable', 'date'],
            'target_to' => ['nullable', 'date'],
            'sort_field' => ['nullable', 'in:plan_number,target_at,status,total_active_minutes,total_wait_minutes,total_recipe_minutes,planned_start_at,created_at'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $filters = ProductionPlanIndexData::from($validated);

        $paginator = $this->service->paginateForAdmin($filters);

        $plans = $paginator->through(fn (ProductionPlan $plan): array => ProductionPlanListItemData::fromModel(
            $plan,
            $this->service->buildPlanPayload($plan),
        )->toArray());

        return Inertia::render(InertiaPage::ADMIN_PRODUCTION_PLANS_INDEX->value, [
            'productionPlans' => $plans,
            'products' => $this->service->listSelectableProducts(),
            'statuses' => $this->service->listStatuses(),
            'filters' => $filters->toFrontendFilters(),
            'summary' => $this->service->buildIndexSummary($filters),
        ]);
    }

    public function createFlow(): Response
    {
        $this->authorize('create', ProductionPlan::class);

        return Inertia::render(InertiaPage::ADMIN_PRODUCTION_PLANS_CREATE_FLOW->value, [
            'products' => $this->service->listActiveProductsForCreateFlow(),
            'statuses' => $this->service->listStatuses(),
        ]);
    }

    public function storeFlow(StoreProductionPlanCreateFlowRequest $request): RedirectResponse
    {
        $plan = $this->createFlowService->create(ProductionPlanStoreData::from($request->validated()), (int) $request->user()->id);

        return redirect()
            ->route('admin.production-plans.show', $plan)
            ->with('success', __('admin.production_plans.flow.saved').'.');
    }

    public function show(ProductionPlan $productionPlan): Response
    {
        $this->authorize('view', $productionPlan);

        return Inertia::render(InertiaPage::ADMIN_PRODUCTION_PLANS_SHOW->value, [
            'plan' => ProductionPlanDetailData::from($this->service->buildPlanPayload($productionPlan))->toArray(),
        ]);
    }

    public function store(StoreProductionPlanRequest $request): RedirectResponse
    {
        $this->authorize('create', ProductionPlan::class);

        $this->service->create(ProductionPlanStoreData::from($request->validated()), (int) $request->user()->id);

        return redirect()
            ->route('admin.production-plans.index')
            ->with('success', __('admin_production_plans.created').'.');
    }

    public function update(UpdateProductionPlanRequest $request, ProductionPlan $productionPlan): RedirectResponse
    {
        $this->authorize('update', $productionPlan);

        $this->service->update($productionPlan, ProductionPlanUpdateData::from($request->validated()));

        return redirect()
            ->route('admin.production-plans.index')
            ->with('success', __('admin_production_plans.updated').'.');
    }

    public function destroy(Request $request, ProductionPlan $productionPlan): RedirectResponse
    {
        $this->authorize('delete', $productionPlan);

        $this->service->delete($productionPlan);

        return redirect()
            ->route('admin.production-plans.index')
            ->with('success', __('admin_production_plans.deleted').'.');
    }
}
