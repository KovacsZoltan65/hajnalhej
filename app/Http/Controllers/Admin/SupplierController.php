<?php

namespace App\Http\Controllers\Admin;

use App\Data\Suppliers\SupplierIndexData;
use App\Data\Suppliers\SupplierListItemData;
use App\Data\Suppliers\SupplierStoreData;
use App\Data\Suppliers\SupplierUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierIndexRequest;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $service) {}

    public function index(SupplierIndexRequest $request): Response
    {
        $this->authorize('viewAny', Supplier::class);

        $filters = SupplierIndexData::from($request->validated());
        $suppliers = $this->service
            ->paginateForAdmin($filters)
            ->through(static fn (Supplier $supplier): array => SupplierListItemData::from($supplier)->toArray());

        return Inertia::render('Admin/Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $filters->toFrontendFilters(),
        ]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->service->create(SupplierStoreData::from($request->validated()), $request->user());

        return back()->with('success', __('admin_supplier.created').'.');
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->service->update($supplier, SupplierUpdateData::from($request->validated()), $request->user());

        return back()->with('success', __('admin_supplier.updated').'.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        $this->service->delete($supplier, request()->user());

        return back()->with('success', __('admin_supplier.deleted').'.');
    }
}
