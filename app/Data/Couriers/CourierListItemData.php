<?php

declare(strict_types=1);

namespace App\Data\Couriers;

use App\Enums\Delivery\VehicleType;
use App\Models\Courier;
use Spatie\LaravelData\Data;

class CourierListItemData extends Data
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $phone,
        public ?string $email,
        public string $status,
        public ?string $vehicle_type,
        public ?string $vehicle_type_label,
        public bool $active,
        public ?string $notes,
        public ?array $meta,
        public ?string $created_at,
    ) {}

    public static function fromModel(Courier $courier): self
    {
        $vehicleType = $courier->vehicle_type === null ? null : VehicleType::tryFrom($courier->vehicle_type);

        return new self(
            id: $courier->id,
            name: $courier->name,
            phone: $courier->phone,
            email: $courier->email,
            status: $courier->status,
            vehicle_type: $vehicleType?->value,
            vehicle_type_label: $vehicleType === null ? null : __($vehicleType->labelKey()),
            active: $courier->active,
            notes: $courier->notes,
            meta: $courier->meta,
            created_at: $courier->created_at?->toDateTimeString(),
        );
    }
}
