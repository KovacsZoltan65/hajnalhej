<?php

namespace App\Http\Controllers\Admin;

use App\Data\Inventory\InventoryAdjustmentData;
use App\Data\Inventory\InventoryLedgerIndexData;
use App\Data\Inventory\InventoryMovementListItemData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InventoryLedgerIndexRequest;
use App\Http\Requests\StoreInventoryAdjustmentRequest;
use App\Http\Requests\StoreWasteEntryRequest;
use App\Models\InventoryMovement;
use App\Repositories\InventoryMovementRepository;
use App\Services\IngredientService;
use App\Services\InventoryDashboardService;
use App\Services\InventoryService;
use App\Services\ProductService;
use App\Support\InertiaPage;
use App\Support\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryMovementRepository $movementRepository,
        private readonly InventoryDashboardService $dashboardService,
        private readonly IngredientService $ingredientService,
        private readonly InventoryService $inventoryService,
        private readonly ProductService $productService,
    ) {}

    public function index(InventoryLedgerIndexRequest $request): Response
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $filters = InventoryLedgerIndexData::from($request->validated());
        $dashboard = $this->dashboardService->dashboard($filters->days);

        $ledger = $this->movementRepository
            ->paginateLedger($filters)
            ->through(static fn (InventoryMovement $movement): array => InventoryMovementListItemData::fromModel($movement)->toArray());

        return InertiaPage::ADMIN_INVENTORY_INDEX->render([
            'dashboard' => $dashboard,
            'ledger' => $ledger,
            'filters' => $filters->toFrontendFilters(),
            'movement_types' => InventoryMovement::movementTypes(),
            'ingredient_options' => $this->ingredientService->listSelectableActive()->values()->all(),
            'product_options' => $this->productService->listSelectableActiveProducts()->values()->all(),
            'waste_reasons' => [
                __('admin_inventory.waste_reason_expired'),
                __('admin_inventory.waste_reason_damaged'),
                __('admin_inventory.waste_reason_manufacturing_defect'),
                __('admin_inventory.waste_reason_spoiled'),
                __('admin_inventory.waste_reason_unknown'),
            ],
            'canExport' => $request->user()?->can(PermissionRegistry::INVENTORY_EXPORT) ?? false,
        ]);
    }

    public function storeWaste(StoreWasteEntryRequest $request): RedirectResponse
    {
        $this->inventoryService->recordWaste($request->validated(), $request->user());

        return back()->with('success', __('admin_inventory.waste_accounting').'.');
    }

    public function storeAdjustment(StoreInventoryAdjustmentRequest $request): RedirectResponse
    {
        $this->inventoryService->recordAdjustment(InventoryAdjustmentData::from($request->validated()), $request->user());

        return back()->with('success', __('admin_inventory.inventory_adjustment_posted').'.');
    }
}
