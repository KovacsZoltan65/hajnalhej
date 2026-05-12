<?php

namespace App\Http\Controllers\Admin;

use App\Data\Purchases\PurchaseIndexData;
use App\Data\Purchases\PurchaseItemData;
use App\Data\Purchases\PurchaseListItemData;
use App\Data\Purchases\PurchaseStoreData;
use App\Data\Purchases\PurchaseUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurchaseIndexRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\IngredientService;
use App\Services\PurchaseService;
use App\Support\InertiaPage;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use RuntimeException;

class PurchaseController extends Controller
{
    public function __construct(
        private readonly PurchaseService $service,
        private readonly IngredientService $ingredientService,
    ) {}

    public function index(PurchaseIndexRequest $request): Response
    {
        $this->authorize('viewAny', Purchase::class);

        $filters = PurchaseIndexData::from($request->validated());
        $purchases = $this->service
            ->paginateForAdmin($filters)
            ->through(static fn (Purchase $purchase): array => PurchaseListItemData::from($purchase)->toArray());

        return InertiaPage::ADMIN_PURCHASES_INDEX->render([
            'purchases' => $purchases,
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
            'ingredient_options' => $this->ingredientService->listSelectableActive()->values()->all(),
            'statuses' => Purchase::statuses(),
            'filters' => $filters->toFrontendFilters(),
        ]);
    }

    public function show(Purchase $purchase): Response
    {
        $this->authorize('view', $purchase);
        $purchase = $this->service->findWithItems($purchase->id) ?? $purchase;

        return InertiaPage::ADMIN_PURCHASES_SHOW->render([
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
                'items' => $purchase->items
                    ->map(static fn ($item): array => PurchaseItemData::from($item)->toArray())
                    ->values()
                    ->all(),
            ],
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
            'ingredient_options' => $this->ingredientService->listSelectableActive()->values()->all(),
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        try {
            $this->service->create(PurchaseStoreData::from($request->validated()), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Beszerzés létrehozva.');
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        try {
            $this->service->update($purchase, PurchaseUpdateData::from($request->validated()), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('admin_purchase.created').'.');
    }

    public function post(Purchase $purchase): RedirectResponse
    {
        $this->authorize('update', $purchase);

        try {
            $this->service->post($purchase, request()->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('admin_purchase.updated').'.');
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        $this->authorize('update', $purchase);

        try {
            $this->service->cancel($purchase, request()->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('admin_purchase.canceled').'.');
    }
}
