<?php

declare(strict_types=1);

namespace App\Data\Orders;

use App\Models\Order;
use Spatie\LaravelData\Data;

class OrderListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $order_number,
        public string $customer_name,
        public string $customer_email,
        public string $customer_phone,
        public string $status,
        public float $total,
        public string $currency,
        public ?string $pickup_date,
        public ?string $pickup_time_slot,
        public ?string $placed_at,
        public int $items_count,
    ) {}

    public static function fromModel(Order $order): self
    {
        return new self(
            id: $order->id,
            order_number: $order->order_number,
            customer_name: $order->customer_name,
            customer_email: $order->customer_email,
            customer_phone: $order->customer_phone,
            status: $order->status,
            total: (float) $order->total,
            currency: $order->currency,
            pickup_date: $order->pickup_date?->toDateString(),
            pickup_time_slot: $order->pickup_time_slot,
            placed_at: $order->placed_at?->toDateTimeString(),
            items_count: (int) ($order->items_count ?? 0),
        );
    }
}
