<?php

namespace App\Services\Audit;

use App\Models\Order;
use App\Models\User;

class OrderAuditService extends BaseAuditService
{
    public const LOG_NAME = 'orders';

    public const ORDER_PLACED = 'order.placed';
    public const ORDER_STATUS_UPDATED = 'order.status.updated';
    public const ORDER_CANCELLED = 'order.cancelled';
    public const ORDER_INTERNAL_NOTE_CREATED = 'order.internal_note.created';
    public const ORDER_INTERNAL_NOTE_UPDATED = 'order.internal_note.updated';
    public const ORDER_PICKUP_UPDATED = 'order.pickup.updated';

    /**
     * @return array<int, string>
     */
    public static function eventKeys(): array
    {
        return [
            self::ORDER_PLACED,
            self::ORDER_STATUS_UPDATED,
            self::ORDER_CANCELLED,
            self::ORDER_INTERNAL_NOTE_CREATED,
            self::ORDER_INTERNAL_NOTE_UPDATED,
            self::ORDER_PICKUP_UPDATED,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logOrderPlaced(Order $order, ?User $actor, array $context = []): void
    {
        $itemsSummary = $order->items
            ->map(fn ($item): array => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name_snapshot' => $item->product_name_snapshot,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ])
            ->values()
            ->all();

        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ORDER_PLACED,
            description: 'Order placed',
            actor: $actor,
            subject: $order,
            before: ['order' => null],
            after: ['order' => $this->orderSnapshot($order)],
            context: $context,
            extraProperties: [
                'order' => $this->orderSnapshot($order),
                'customer_snapshot' => [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                    'user_id' => $order->user_id,
                    'is_guest_checkout' => $order->user_id === null,
                ],
                'totals_snapshot' => [
                    'subtotal' => (float) $order->subtotal,
                    'total' => (float) $order->total,
                    'currency' => $order->currency,
                ],
                'pickup_snapshot' => [
                    'pickup_date' => $order->pickup_date?->toDateString(),
                    'pickup_time_slot' => $order->pickup_time_slot,
                ],
                'items_summary' => $itemsSummary,
            ],
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logOrderStatusUpdated(
        Order $order,
        User $actor,
        string $fromStatus,
        string $toStatus,
        array $context = [],
    ): void {
        $event = $toStatus === Order::STATUS_CANCELLED ? self::ORDER_CANCELLED : self::ORDER_STATUS_UPDATED;
        $description = $toStatus === Order::STATUS_CANCELLED ? 'Order cancelled' : 'Order status updated';

        $this->log(
            logName: self::LOG_NAME,
            eventKey: $event,
            description: $description,
            actor: $actor,
            subject: $order,
            before: ['status' => $fromStatus],
            after: ['status' => $toStatus],
            context: $context,
            extraProperties: [
                'order' => $this->orderSnapshot($order),
                'status_transition' => [
                    'from' => $fromStatus,
                    'to' => $toStatus,
                ],
            ],
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logInternalNoteCreated(
        Order $order,
        User $actor,
        ?string $note,
        array $context = [],
    ): void {
        $sanitized = $this->sanitizeNote($note);

        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ORDER_INTERNAL_NOTE_CREATED,
            description: 'Order internal note created',
            actor: $actor,
            subject: $order,
            before: ['internal_note' => null],
            after: ['internal_note' => $sanitized],
            context: $context,
            extraProperties: [
                'order' => $this->orderSnapshot($order),
                'note_summary' => [
                    'preview' => $this->notePreview($sanitized),
                    'length' => mb_strlen((string) $sanitized),
                ],
            ],
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logInternalNoteUpdated(
        Order $order,
        User $actor,
        ?string $beforeNote,
        ?string $afterNote,
        array $context = [],
    ): void {
        $beforeSanitized = $this->sanitizeNote($beforeNote);
        $afterSanitized = $this->sanitizeNote($afterNote);

        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ORDER_INTERNAL_NOTE_UPDATED,
            description: 'Order internal note updated',
            actor: $actor,
            subject: $order,
            before: ['internal_note' => $beforeSanitized],
            after: ['internal_note' => $afterSanitized],
            context: $context,
            extraProperties: [
                'order' => $this->orderSnapshot($order),
                'note_summary' => [
                    'before_preview' => $this->notePreview($beforeSanitized),
                    'after_preview' => $this->notePreview($afterSanitized),
                    'before_length' => mb_strlen((string) $beforeSanitized),
                    'after_length' => mb_strlen((string) $afterSanitized),
                ],
            ],
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logPickupUpdated(
        Order $order,
        User $actor,
        ?string $beforeDate,
        ?string $beforeSlot,
        ?string $afterDate,
        ?string $afterSlot,
        array $context = [],
    ): void {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ORDER_PICKUP_UPDATED,
            description: 'Order pickup updated',
            actor: $actor,
            subject: $order,
            before: [
                'pickup_date' => $beforeDate,
                'pickup_time_slot' => $beforeSlot,
            ],
            after: [
                'pickup_date' => $afterDate,
                'pickup_time_slot' => $afterSlot,
            ],
            context: $context,
            extraProperties: [
                'order' => $this->orderSnapshot($order),
                'pickup_transition' => [
                    'from' => [
                        'pickup_date' => $beforeDate,
                        'pickup_time_slot' => $beforeSlot,
                    ],
                    'to' => [
                        'pickup_date' => $afterDate,
                        'pickup_time_slot' => $afterSlot,
                    ],
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function orderSnapshot(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'currency' => $order->currency,
            'total' => (float) $order->total,
        ];
    }

    private function sanitizeNote(?string $note): ?string
    {
        $value = trim((string) $note);

        return $value === '' ? null : $value;
    }

    private function notePreview(?string $note): ?string
    {
        if ($note === null) {
            return null;
        }

        return mb_substr($note, 0, 120);
    }
}
