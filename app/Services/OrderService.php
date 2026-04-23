<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Services\Audit\OrderAuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use RuntimeException;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly OrderAuditService $auditService,
        private readonly ProductionInventoryService $productionInventoryService,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
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

    /**
     * @param array<string, mixed> $payload
     */
    public function transitionStatus(Order $order, array $payload, ?User $actor = null): Order
    {
        $targetStatus = (string) ($payload['status'] ?? '');
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

    /**
     * @param array<int, array<string, mixed>> $lineSnapshots
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
}
