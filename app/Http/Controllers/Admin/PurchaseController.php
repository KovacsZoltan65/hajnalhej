<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurchaseIndexRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\IngredientService;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class PurchaseController extends Controller
{
    /**
     * @param PurchaseService $service
     * @param IngredientService $ingredientService
     */
    public function __construct(
        private readonly PurchaseService $service,
        private readonly IngredientService $ingredientService,
    ) {
    }

    /**
     * @param PurchaseIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(PurchaseIndexRequest $request): Response
    {
        $this->authorize('viewAny', Purchase::class);

        $filters = $request->validated();
        $purchases = $this->service->paginateForAdmin($filters)->through(static fn (Purchase $purchase): array => [
            'id' => $purchase->id,
            'supplier_id' => $purchase->supplier_id,
            'supplier_name' => $purchase->supplier?->name,
            'reference_number' => $purchase->reference_number,
            'purchase_date' => $purchase->purchase_date?->toDateString(),
            'status' => $purchase->status,
            'subtotal' => (float) $purchase->subtotal,
            'total' => (float) $purchase->total,
            'notes' => $purchase->notes,
            'items_count' => (int) ($purchase->items_count ?? 0),
            'posted_at' => $purchase->posted_at?->toDateTimeString(),
            'created_by' => $purchase->creator?->name,
        ]);

        return Inertia::render('Admin/Purchases/Index', [
            'purchases' => $purchases,
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
            'ingredient_options' => $this->ingredientService->listSelectableActive()->values()->all(),
            'statuses' => Purchase::statuses(),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'status' => (string) ($filters['status'] ?? ''),
                'supplier_id' => $filters['supplier_id'] ?? '',
                'sort_field' => (string) ($filters['sort_field'] ?? 'purchase_date'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'desc'),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
        ]);
    }

    /**
     * @param Purchase $purchase
     * @return \Inertia\Response
     */
    public function show(Purchase $purchase): Response
    {
        $this->authorize('view', $purchase);
        $purchase = $this->service->findWithItems($purchase->id) ?? $purchase;

        return Inertia::render('Admin/Purchases/Show', [
            'purchase' => [
                'id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'supplier_name' => $purchase->supplier?->name,
                'reference_number' => $purchase->reference_number,
                'purchase_date' => $purchase->purchase_date?->toDateString(),
                'status' => $purchase->status,
                'subtotal' => (float) $purchase->subtotal,
                'total' => (float) $purchase->total,
                'notes' => $purchase->notes,
                'posted_at' => $purchase->posted_at?->toDateTimeString(),
                'items' => $purchase->items->map(static fn ($item): array => [
                    'id' => $item->id,
                    'ingredient_id' => $item->ingredient_id,
                    'ingredient_name' => $item->ingredient?->name,
                    'ingredient_unit' => $item->ingredient?->unit,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->unit,
                    'unit_cost' => (float) $item->unit_cost,
                    'line_total' => (float) $item->line_total,
                ])->values()->all(),
            ],
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
            'ingredient_options' => $this->ingredientService->listSelectableActive()->values()->all(),
        ]);
    }

    /**
     * @param StorePurchaseRequest $request
     * @return RedirectResponse
     */
    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        try {
            $this->service->create($request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Beszerzés létrehozva.');
    }

    /**
     * @param UpdatePurchaseRequest $request
     * @param Purchase $purchase
     * @return RedirectResponse
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        try {
            $this->service->update($purchase, $request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Beszerzés frissítve.');
    }

    /**
     * @param Purchase $purchase
     * @return RedirectResponse
     */
    public function post(Purchase $purchase): RedirectResponse
    {
        $this->authorize('update', $purchase);

        try {
            $this->service->post($purchase, request()->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Beszerzés könyvelve, készlet frissítve.');
    }

    /**
     * @param Purchase $purchase
     * @return RedirectResponse
     */
    public function cancel(Purchase $purchase): RedirectResponse
    {
        $this->authorize('update', $purchase);

        try {
            $this->service->cancel($purchase, request()->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Beszerzés stornózva.');
    }
}
