<?php

namespace App\Services;

use App\Data\Orders\OrderIndexData;
use App\Data\Orders\OrderStatusUpdateData;
use App\Enums\Delivery\DeliveryStatus;
use App\Enums\Orders\FulfillmentMethod;
use App\Models\Courier;
use App\Models\Order;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Services\Audit\OrderAuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly OrderAuditService $auditService,
        private readonly ProductionInventoryService $productionInventoryService,
    ) {}

    public function paginateForAdmin(OrderIndexData $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    /**
     * @return array<int, string>
     */
    public function statuses(): array
    {
        return Order::statuses();
    }

    public function transitionStatus(Order $order, OrderStatusUpdateData $data, ?User $actor = null): Order
    {
        $payload = $data->toPayload();
        $targetStatus = $data->status;
        $beforeStatus = $order->status;
        $beforeNotes = $this->normalizeText($order->internal_notes);
        $beforePickupDate = $order->pickup_date?->toDateString();
        $beforePickupSlot = $this->normalizeText($order->pickup_time_slot);

        $updatePayload = [
            'status' => $targetStatus,
            'internal_notes' => $payload['internal_notes'] ?? $order->internal_notes,
            'pickup_date' => $payload['pickup_date'] ?? $order->pickup_date?->toDateString(),
            'pickup_time_slot' => $payload['pickup_time_slot'] ?? $order->pickup_time_slot,
            'confirmed_at' => $order->confirmed_at,
            'completed_at' => $order->completed_at,
            'cancelled_at' => $order->cancelled_at,
        ];

        if ($targetStatus === $order->status) {
            $updated = $this->repository->update($order, $updatePayload);

            $this->logNonStatusChanges($updated, $actor, $beforeNotes, $beforePickupDate, $beforePickupSlot);

            return $updated;
        }

        if (! $order->canTransitionTo($targetStatus)) {
            throw new RuntimeException(__('commerce.orders.invalid_status_transition'));
        }

        if ($targetStatus === Order::STATUS_CONFIRMED && $order->confirmed_at === null) {
            $updatePayload['confirmed_at'] = Carbon::now();
        }

        if ($targetStatus === Order::STATUS_COMPLETED && $order->completed_at === null) {
            $updatePayload['completed_at'] = Carbon::now();
        }

        if ($targetStatus === Order::STATUS_CANCELLED && $order->cancelled_at === null) {
            $updatePayload['cancelled_at'] = Carbon::now();
        }

        $updated = $this->repository->update($order, $updatePayload);

        if ($targetStatus === Order::STATUS_COMPLETED) {
            $this->productionInventoryService->consumeForOrder($updated, $actor);
            $updated = $updated->refresh();
        }

        if ($actor !== null) {
            $this->auditService->logOrderStatusUpdated(
                order: $updated,
                actor: $actor,
                fromStatus: $beforeStatus,
                toStatus: $updated->status,
                context: ['operation' => 'admin.order.status.update'],
            );
        }

        $this->logNonStatusChanges($updated, $actor, $beforeNotes, $beforePickupDate, $beforePickupSlot);

        return $updated;
    }

    public function assignCourier(Order $order, Courier $courier, ?User $actor = null): Order
    {
        return DB::transaction(function () use ($order, $courier, $actor): Order {
            $order->refresh()->load('courier');
            $courier->refresh();

            if ($order->fulfillment_method !== FulfillmentMethod::DELIVERY->value) {
                throw new RuntimeException(__('delivery.errors.only_delivery_orders'));
            }

            if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED], true)) {
                throw new RuntimeException(__('delivery.errors.cannot_assign'));
            }

            if (! $this->deliveryStatus($order)->canAssignCourier()) {
                throw new RuntimeException(__('delivery.errors.cannot_assign'));
            }

            if (! $courier->active || $courier->status !== Courier::STATUS_ACTIVE) {
                throw new RuntimeException(__('delivery.errors.inactive_courier'));
            }

            $previousCourier = $order->courier;

            $updated = $this->repository->update($order, [
                'courier_id' => $courier->id,
                'delivery_status' => DeliveryStatus::ASSIGNED->value,
                'failed_delivery_reason' => null,
            ])->load('courier');

            activity('orders')
                ->event('order.courier_assigned')
                ->performedOn($updated)
                ->causedBy($actor)
                ->withProperties([
                    'order_id' => $updated->id,
                    'order_number' => $updated->order_number,
                    'previous_courier_id' => $previousCourier?->id,
                    'previous_courier_name' => $previousCourier?->name,
                    'new_courier_id' => $courier->id,
                    'new_courier_name' => $courier->name,
                    'user_id' => $actor?->id,
                ])
                ->log('Courier assigned to order');

            return $updated;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineSnapshots
     * @return array<string, mixed>
     */
    public function buildFutureProductionMetadata(array $lineSnapshots): array
    {
        return [
            'version' => 1,
            'production_ready' => true,
            'line_count' => count($lineSnapshots),
            'created_for' => 'future_bom_aggregation',
        ];
    }

    private function logNonStatusChanges(
        Order $updatedOrder,
        ?User $actor,
        ?string $beforeNotes,
        ?string $beforePickupDate,
        ?string $beforePickupSlot,
    ): void {
        if ($actor === null) {
            return;
        }

        $afterNotes = $this->normalizeText($updatedOrder->internal_notes);
        $afterPickupDate = $updatedOrder->pickup_date?->toDateString();
        $afterPickupSlot = $this->normalizeText($updatedOrder->pickup_time_slot);

        if ($beforeNotes !== $afterNotes) {
            if ($beforeNotes === null && $afterNotes !== null) {
                $this->auditService->logInternalNoteCreated(
                    order: $updatedOrder,
                    actor: $actor,
                    note: $afterNotes,
                    context: ['operation' => 'admin.order.internal_note.create'],
                );
            } else {
                $this->auditService->logInternalNoteUpdated(
                    order: $updatedOrder,
                    actor: $actor,
                    beforeNote: $beforeNotes,
                    afterNote: $afterNotes,
                    context: ['operation' => 'admin.order.internal_note.update'],
                );
            }
        }

        if ($beforePickupDate !== $afterPickupDate || $beforePickupSlot !== $afterPickupSlot) {
            $this->auditService->logPickupUpdated(
                order: $updatedOrder,
                actor: $actor,
                beforeDate: $beforePickupDate,
                beforeSlot: $beforePickupSlot,
                afterDate: $afterPickupDate,
                afterSlot: $afterPickupSlot,
                context: ['operation' => 'admin.order.pickup.update'],
            );
        }
    }

    private function normalizeText(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function deliveryStatus(Order $order): DeliveryStatus
    {
        if ($order->delivery_status === null) {
            return DeliveryStatus::PENDING;
        }

        return DeliveryStatus::from($order->delivery_status);
    }
}
