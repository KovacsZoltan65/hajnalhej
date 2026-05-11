<?php

declare(strict_types=1);

namespace App\Data\Orders;

use App\Models\Order;
use Spatie\LaravelData\Data;

class OrderDetailData extends Data
{
    /**
     * @param  array<int, OrderItemData>  $items
     */
    public function __construct(
        public int $id,
        public string $order_number,
        public string $status,
        public string $customer_name,
        public string $customer_email,
        public string $customer_phone,
        public ?string $pickup_date,
        public ?string $pickup_time_slot,
        public ?string $notes,
        public ?string $internal_notes,
        public float $subtotal,
        public float $total,
        public string $currency,
        public ?string $placed_at,
        public ?string $confirmed_at,
        public ?string $completed_at,
        public ?string $cancelled_at,
        public array $items,
    ) {}

    public static function fromModel(Order $order): self
    {
        return new self(
            id: $order->id,
            order_number: $order->order_number,
            status: $order->status,
            customer_name: $order->customer_name,
            customer_email: $order->customer_email,
            customer_phone: $order->customer_phone,
            pickup_date: $order->pickup_date?->toDateString(),
            pickup_time_slot: $order->pickup_time_slot,
            notes: $order->notes,
            internal_notes: $order->internal_notes,
            subtotal: (float) $order->subtotal,
            total: (float) $order->total,
            currency: $order->currency,
            placed_at: $order->placed_at?->toDateTimeString(),
            confirmed_at: $order->confirmed_at?->toDateTimeString(),
            completed_at: $order->completed_at?->toDateTimeString(),
            cancelled_at: $order->cancelled_at?->toDateTimeString(),
            items: $order->items
                ->map(fn ($item): OrderItemData => OrderItemData::fromModel($item))
                ->values()
                ->all(),
        );
    }
}
