<?php

declare(strict_types=1);

namespace App\Enums\Delivery;

enum VehicleType: string
{
    case WALKING = 'walking';
    case BICYCLE = 'bicycle';
    case SCOOTER = 'scooter';
    case CAR = 'car';
    case VAN = 'van';

    public function labelKey(): string
    {
        return "delivery.vehicle_types.{$this->value}";
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $type): array => [
                'value' => $type->value,
                'label' => __($type->labelKey()),
            ],
            self::cases(),
        );
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
