<?php

declare(strict_types=1);

namespace App\Data\Couriers;

use App\Enums\Delivery\VehicleType;
use App\Models\Courier;
use Spatie\LaravelData\Data;

class CourierFormOptionsData extends Data
{
    /**
     * @param  array<int, array{value:string,label:string}>  $vehicleTypes
     * @param  array<int, array{value:string,label:string}>  $statusOptions
     */
    public function __construct(
        public array $vehicleTypes,
        public array $statusOptions,
    ) {}

    public static function make(): self
    {
        return new self(
            vehicleTypes: VehicleType::options(),
            statusOptions: [
                ['value' => '', 'label' => __('common.all')],
                ['value' => Courier::STATUS_ACTIVE, 'label' => __('common.active')],
                ['value' => Courier::STATUS_INACTIVE, 'label' => __('common.inactive')],
            ],
        );
    }
}
