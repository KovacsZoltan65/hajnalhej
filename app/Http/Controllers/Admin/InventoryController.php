<?php

namespace App\Http\Controllers\Admin;

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
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryMovementRepository $movementRepository,
        private readonly InventoryDashboardService $dashboardService,
        private readonly IngredientService $ingredientService,
        private readonly InventoryService $inventoryService,
        private readonly ProductService $productService,
    ) {
    }

    public function index(InventoryLedgerIndexRequest $request): Response
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $filters = $request->validated();
        $days = (int) ($filters['days'] ?? 7);
        $dashboard = $this->dashboardService->dashboard($days);

        $ledger = $this->movementRepository->paginateLedger($filters)->through(static fn (InventoryMovement $movement): array => [
            'id' => $movement->id,
            'ingredient_id' => $movement->ingredient_id,
            'ingredient_name' => $movement->ingredient?->name,
            'ingredient_unit' => $movement->ingredient?->unit,
            'movement_type' => $movement->movement_type,
            'direction' => $movement->direction,
            'quantity' => (float) $movement->quantity,
            'unit_cost' => $movement->unit_cost !== null ? (float) $movement->unit_cost : null,
            'total_cost' => $movement->total_cost !== null ? (float) $movement->total_cost : null,
            'occurred_at' => $movement->occurred_at?->toDateTimeString(),
            'reference_type' => $movement->reference_type,
            'reference_id' => $movement->reference_id,
            'notes' => $movement->notes,
            'created_by' => $movement->creator?->name,
        ]);

        return Inertia::render('Admin/Inventory/Index', [
            'dashboard' => $dashboard,
            'ledger' => $ledger,
            'filters' => [
                'days' => $days,
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
                'ingredient_id' => $filters['ingredient_id'] ?? '',
                'movement_type' => $filters['movement_type'] ?? '',
                'search' => (string) ($filters['search'] ?? ''),
                'per_page' => (int) ($filters['per_page'] ?? 15),
            ],
            'movement_types' => InventoryMovement::movementTypes(),
            'ingredient_options' => $this->ingredientService->listSelectableActive()->values()->all(),
            'product_options' => $this->productService->listSelectableActiveProducts()->values()->all(),
            'waste_reasons' => ['lejárt', 'sérült', 'gyártási hiba', 'romlott', 'ismeretlen'],
        ]);
    }

    public function storeWaste(StoreWasteEntryRequest $request): RedirectResponse
    {
        $this->inventoryService->recordWaste($request->validated(), $request->user());

        return back()->with('success', 'Selejt könyvelve.');
    }

    public function storeAdjustment(StoreInventoryAdjustmentRequest $request): RedirectResponse
    {
        $this->inventoryService->recordAdjustment($request->validated(), $request->user());

        return back()->with('success', 'Készletkorrekció könyvelve.');
    }
}
