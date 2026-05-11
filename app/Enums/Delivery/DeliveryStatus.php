<?php

declare(strict_types=1);

namespace App\Enums\Delivery;

enum DeliveryStatus: string
{
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function labelKey(): string
    {
        return "delivery.statuses.{$this->value}";
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $status): array => [
                'value' => $status->value,
                'label' => __($status->labelKey()),
            ],
            self::cases(),
        );
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::DELIVERED, self::FAILED, self::CANCELLED], true);
    }

    public function canAssignCourier(): bool
    {
        return in_array($this, [self::PENDING, self::ASSIGNED], true);
    }

    public function canStartDelivery(): bool
    {
        return $this === self::ASSIGNED;
    }

    public function canMarkDelivered(): bool
    {
        return $this === self::OUT_FOR_DELIVERY;
    }

    public function canMarkFailed(): bool
    {
        return in_array($this, [self::ASSIGNED, self::OUT_FOR_DELIVERY], true);
    }
}
