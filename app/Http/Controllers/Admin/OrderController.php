<?php

namespace App\Http\Controllers\Admin;

use App\Data\Couriers\CourierListItemData;
use App\Data\Orders\DeliveryAssignData;
use App\Data\Orders\DeliveryFailedData;
use App\Data\Orders\OrderDetailData;
use App\Data\Orders\OrderIndexData;
use App\Data\Orders\OrderListItemData;
use App\Data\Orders\OrderStatusUpdateData;
use App\Enums\Delivery\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\Delivery\AssignCourierRequest;
use App\Http\Requests\Admin\Order\Delivery\MarkDeliveryFailedRequest;
use App\Http\Requests\Admin\OrderIndexRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\CourierService;
use App\Services\DeliveryService;
use App\Services\OrderService;
use App\Support\InertiaPage;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $service,
        private readonly CourierService $courierService,
        private readonly DeliveryService $deliveryService,
    ) {}

    public function index(OrderIndexRequest $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $filters = OrderIndexData::from($request->validated());

        $orders = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Order $order): array => OrderListItemData::fromModel($order)->toArray());

        return Inertia::render(InertiaPage::ADMIN_ORDERS_INDEX->value, [
            'orders' => $orders,
            'statusOptions' => $this->service->statuses(),
            'filters' => $filters->toFrontendFilters(),
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load(['items.product:id,name,slug', 'pickupBranch:id,name,code,type,address', 'courier:id,name,phone,email,vehicle_type']);

        return Inertia::render(InertiaPage::ADMIN_ORDERS_SHOW->value, [
            'order' => OrderDetailData::fromModel($order)->toArray(),
            'statusOptions' => $this->service->statuses(),
            'courierOptions' => $this->courierService
                ->activeOptions()
                ->map(fn ($courier): array => CourierListItemData::fromModel($courier)->toArray())
                ->values()
                ->all(),
            'deliveryStatusOptions' => DeliveryStatus::options(),
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

    public function assignCourier(AssignCourierRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->deliveryService->assignCourier($order, DeliveryAssignData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('delivery.messages.assigned'));
    }

    public function startDelivery(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->deliveryService->startDelivery($order);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('delivery.messages.started'));
    }

    public function markDelivered(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->deliveryService->markDelivered($order);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('delivery.messages.delivered'));
    }

    public function markFailed(MarkDeliveryFailedRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->deliveryService->markFailed($order, DeliveryFailedData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('delivery.messages.failed'));
    }

    public function cancelDelivery(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->deliveryService->cancelDelivery($order);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('delivery.messages.cancelled'));
    }
}
