<?php

namespace App\Http\Controllers\Admin;

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
    /**
     * @param SupplierService $service
     */
    public function __construct(private readonly SupplierService $service)
    {
    }

    /**
     * @param SupplierIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(SupplierIndexRequest $request): Response
    {
        $this->authorize('viewAny', Supplier::class);

        $filters = $request->validated();
        $suppliers = $this->service->paginateForAdmin($filters)->through(static fn (Supplier $supplier): array => [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'tax_number' => $supplier->tax_number,
            'lead_time_days' => $supplier->lead_time_days,
            'notes' => $supplier->notes,
            'purchases_count' => (int) ($supplier->purchases_count ?? 0),
            'created_at' => $supplier->created_at?->toDateTimeString(),
            'updated_at' => $supplier->updated_at?->toDateTimeString(),
        ]);

        return Inertia::render('Admin/Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'sort_field' => (string) ($filters['sort_field'] ?? 'name'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'asc'),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
        ]);
    }

    /**
     * @param StoreSupplierRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $request->user());

        return back()->with('success', __('admin_supplier.created') . '.');
    }

    /**
     * @param UpdateSupplierRequest $request
     * @param Supplier $supplier
     * @return RedirectResponse
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->service->update($supplier, $request->validated(), $request->user());

        return back()->with('success', __('admin_supplier.updated') . '.');
    }

    /**
     * @param Supplier $supplier
     * @return RedirectResponse
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        $this->service->delete($supplier, request()->user());

        return back()->with('success', __('admin_supplier.deleted') . '.');
    }
}
