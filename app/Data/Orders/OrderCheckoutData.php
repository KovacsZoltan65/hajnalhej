<?php

declare(strict_types=1);

namespace App\Data\Orders;

use App\Enums\Orders\FulfillmentMethod;
use Spatie\LaravelData\Data;

class OrderCheckoutData extends Data
{
    public function __construct(
        public string $customer_name,
        public string $customer_email,
        public string $customer_phone,
        public ?string $notes,
        public ?string $pickup_date,
        public ?string $pickup_time_slot,
        public string $fulfillment_method,
        public ?int $pickup_branch_id,
        public OrderAddressData $billing_address,
        public ?OrderAddressData $shipping_address,
        public bool $same_as_billing,
        public ?string $delivery_notes,
    ) {}

    public function method(): FulfillmentMethod
    {
        return FulfillmentMethod::from($this->fulfillment_method);
    }

    /**
     * @return array<string, mixed>
     */
    public function billingSnapshot(): array
    {
        return $this->billing_address->toSnapshot();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function shippingSnapshot(): ?array
    {
        if ($this->method()->isPickup()) {
            return null;
        }

        return ($this->same_as_billing ? $this->billing_address : $this->shipping_address)?->toSnapshot();
    }

    public function normalizedDeliveryNotes(): ?string
    {
        if ($this->method()->isPickup()) {
            return null;
        }

        $notes = trim((string) $this->delivery_notes);

        return $notes === '' ? null : $notes;
    }
}
