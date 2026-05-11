<?php

namespace App\Http\Controllers\Admin;

use App\Data\Orders\OrderDetailData;
use App\Data\Orders\OrderIndexData;
use App\Data\Orders\OrderListItemData;
use App\Data\Orders\OrderStatusUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderIndexRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $service) {}

    public function index(OrderIndexRequest $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $filters = OrderIndexData::from($request->validated());

        $orders = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Order $order): array => OrderListItemData::fromModel($order)->toArray());

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'statusOptions' => $this->service->statuses(),
            'filters' => $filters->toFrontendFilters(),
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load(['items.product:id,name,slug', 'pickupBranch:id,name,code,type,address']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => OrderDetailData::fromModel($order)->toArray(),
            'statusOptions' => $this->service->statuses(),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->service->transitionStatus($order, OrderStatusUpdateData::from($request->validated()), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('commerce.admin.status_updated'));
    }
}
