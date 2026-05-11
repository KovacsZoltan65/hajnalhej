<?php

declare(strict_types=1);

namespace App\Data\Orders;

use App\Enums\Delivery\DeliveryStatus;
use App\Enums\Orders\FulfillmentMethod;
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
        public string $fulfillment_method,
        public string $fulfillment_label,
        public ?array $pickup_branch,
        public ?array $billing_address_snapshot,
        public ?array $shipping_address_snapshot,
        public ?string $delivery_notes,
        public float $delivery_fee,
        public ?string $delivery_status,
        public ?string $delivery_status_label,
        public ?array $courier,
        public ?string $delivery_scheduled_at,
        public ?string $out_for_delivery_at,
        public ?string $delivered_at,
        public ?string $failed_delivery_reason,
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
        $fulfillmentMethod = FulfillmentMethod::tryFrom($order->fulfillment_method)
            ?? FulfillmentMethod::PICKUP;
        $deliveryStatus = $order->delivery_status === null ? null : DeliveryStatus::tryFrom($order->delivery_status);

        return new self(
            id: $order->id,
            order_number: $order->order_number,
            status: $order->status,
            customer_name: $order->customer_name,
            customer_email: $order->customer_email,
            customer_phone: $order->customer_phone,
            pickup_date: $order->pickup_date?->toDateString(),
            pickup_time_slot: $order->pickup_time_slot,
            fulfillment_method: $fulfillmentMethod->value,
            fulfillment_label: __($fulfillmentMethod->labelKey()),
            pickup_branch: $order->pickupBranch === null ? null : [
                'id' => $order->pickupBranch->id,
                'name' => $order->pickupBranch->name,
                'code' => $order->pickupBranch->code,
                'type' => $order->pickupBranch->type,
                'address' => $order->pickupBranch->address,
            ],
            billing_address_snapshot: $order->billing_address_snapshot,
            shipping_address_snapshot: $order->shipping_address_snapshot,
            delivery_notes: $order->delivery_notes,
            delivery_fee: (float) $order->delivery_fee,
            delivery_status: $deliveryStatus?->value,
            delivery_status_label: $deliveryStatus === null ? null : __($deliveryStatus->labelKey()),
            courier: $order->courier === null ? null : [
                'id' => $order->courier->id,
                'name' => $order->courier->name,
                'phone' => $order->courier->phone,
                'email' => $order->courier->email,
                'vehicle_type' => $order->courier->vehicle_type,
            ],
            delivery_scheduled_at: $order->delivery_scheduled_at?->toDateTimeString(),
            out_for_delivery_at: $order->out_for_delivery_at?->toDateTimeString(),
            delivered_at: $order->delivered_at?->toDateTimeString(),
            failed_delivery_reason: $order->failed_delivery_reason,
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
