<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Data\Couriers\CourierFormOptionsData;
use App\Data\Couriers\CourierIndexData;
use App\Data\Couriers\CourierListItemData;
use App\Data\Couriers\CourierStoreData;
use App\Data\Couriers\CourierUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Courier\IndexCourierRequest;
use App\Http\Requests\Admin\Courier\StoreCourierRequest;
use App\Http\Requests\Admin\Courier\UpdateCourierRequest;
use App\Models\Courier;
use App\Services\CourierService;
use App\Support\InertiaPage;
use App\Support\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CourierController extends Controller
{
    public function __construct(private readonly CourierService $service) {}

    public function index(IndexCourierRequest $request): Response
    {
        $filters = CourierIndexData::from($request->validated());

        $paginator = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Courier $courier): array => CourierListItemData::fromModel($courier)->toArray());

        return Inertia::render(InertiaPage::ADMIN_COURIERS_INDEX->value, [
            'couriers' => $paginator,
            'filters' => $filters->toFrontendFilters(),
            'options' => CourierFormOptionsData::make()->toArray(),
            'can' => [
                'create' => $request->user()?->can('create', Courier::class) ?? false,
                'update' => $request->user()?->can(PermissionRegistry::COURIERS_UPDATE) ?? false,
                'delete' => $request->user()?->can(PermissionRegistry::COURIERS_DELETE) ?? false,
            ],
        ]);
    }

    public function store(StoreCourierRequest $request): RedirectResponse
    {
        $this->service->create(CourierStoreData::from($request->validated()));

        return redirect()
            ->route('admin.couriers.index')
            ->with('success', __('admin_couriers.courier_created').'.');
    }

    public function update(UpdateCourierRequest $request, Courier $courier): RedirectResponse
    {
        $this->service->update($courier, CourierUpdateData::from($request->validated()));

        return redirect()
            ->route('admin.couriers.index')
            ->with('success', __('admin_couriers.courier_updated').'.');
    }

    public function destroy(Courier $courier): RedirectResponse
    {
        $this->authorize('delete', $courier);

        $this->service->delete($courier);

        return redirect()
            ->route('admin.couriers.index')
            ->with('success', __('admin_couriers.courier_deleted').'.');
    }
}
