<?php

namespace App\Http\Controllers\Admin;

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
    public function __construct(private readonly OrderService $service)
    {
    }

    /**
     * @param OrderIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(OrderIndexRequest $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $filters = $request->validated();

        $orders = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Order $order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'status' => $order->status,
                'total' => (float) $order->total,
                'currency' => $order->currency,
                'pickup_date' => $order->pickup_date?->toDateString(),
                'pickup_time_slot' => $order->pickup_time_slot,
                'placed_at' => $order->placed_at?->toDateTimeString(),
                'items_count' => $order->items_count,
            ]);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'statusOptions' => $this->service->statuses(),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'status' => (string) ($filters['status'] ?? ''),
                'sort_field' => (string) ($filters['sort_field'] ?? 'placed_at'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'desc'),
                'per_page' => (int) ($filters['per_page'] ?? 15),
            ],
        ]);
    }

    /**
     * @param Order $order
     * @return \Inertia\Response
     */
    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load(['items.product:id,name,slug']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'pickup_date' => $order->pickup_date?->toDateString(),
                'pickup_time_slot' => $order->pickup_time_slot,
                'notes' => $order->notes,
                'internal_notes' => $order->internal_notes,
                'subtotal' => (float) $order->subtotal,
                'total' => (float) $order->total,
                'currency' => $order->currency,
                'placed_at' => $order->placed_at?->toDateTimeString(),
                'confirmed_at' => $order->confirmed_at?->toDateTimeString(),
                'completed_at' => $order->completed_at?->toDateTimeString(),
                'cancelled_at' => $order->cancelled_at?->toDateTimeString(),
                'items' => $order->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name_snapshot' => $item->product_name_snapshot,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => (int) $item->quantity,
                    'line_total' => (float) $item->line_total,
                ])->values()->all(),
            ],
            'statusOptions' => $this->service->statuses(),
        ]);
    }

    /**
     * @param UpdateOrderStatusRequest $request
     * @param Order $order
     * @return RedirectResponse
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->service->transitionStatus($order, $request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('commerce.admin.status_updated'));
    }
}
