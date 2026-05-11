<?php

declare(strict_types=1);

namespace App\Enums\Orders;

enum FulfillmentMethod: string
{
    case PICKUP = 'pickup';
    case DELIVERY = 'delivery';

    public function labelKey(): string
    {
        return "orders.fulfillment.{$this->value}";
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $method): array => [
                'value' => $method->value,
                'label' => __($method->labelKey()),
            ],
            self::cases(),
        );
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $method): string => $method->value, self::cases());
    }

    public function isPickup(): bool
    {
        return $this === self::PICKUP;
    }

    public function isDelivery(): bool
    {
        return $this === self::DELIVERY;
    }
}
