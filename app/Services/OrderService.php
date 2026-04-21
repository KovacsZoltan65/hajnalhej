<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use RuntimeException;

class OrderService
{
    public function __construct(private readonly OrderRepository $repository)
    {
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
    public function transitionStatus(Order $order, array $payload): Order
    {
        $targetStatus = (string) ($payload['status'] ?? '');

        if ($targetStatus === $order->status) {
            return $this->repository->update($order, [
                'internal_notes' => $payload['internal_notes'] ?? $order->internal_notes,
            ]);
        }

        if (! $order->canTransitionTo($targetStatus)) {
            throw new RuntimeException(__('commerce.orders.invalid_status_transition'));
        }

        $timestampPayload = [
            'status' => $targetStatus,
            'internal_notes' => $payload['internal_notes'] ?? $order->internal_notes,
            'confirmed_at' => $order->confirmed_at,
            'completed_at' => $order->completed_at,
            'cancelled_at' => $order->cancelled_at,
        ];

        if ($targetStatus === Order::STATUS_CONFIRMED && $order->confirmed_at === null) {
            $timestampPayload['confirmed_at'] = Carbon::now();
        }

        if ($targetStatus === Order::STATUS_COMPLETED && $order->completed_at === null) {
            $timestampPayload['completed_at'] = Carbon::now();
        }

        if ($targetStatus === Order::STATUS_CANCELLED && $order->cancelled_at === null) {
            $timestampPayload['cancelled_at'] = Carbon::now();
        }

        return $this->repository->update($order, $timestampPayload);
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
}
