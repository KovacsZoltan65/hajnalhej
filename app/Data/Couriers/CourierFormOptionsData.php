<?php

declare(strict_types=1);

namespace App\Data\Couriers;

use App\Enums\Delivery\VehicleType;
use Spatie\LaravelData\Data;

class CourierFormOptionsData extends Data
{
    /**
     * @param  array<int, array{value:string,label:string}>  $vehicleTypes
     * @param  array<int, array{value:string,label:string}>  $activeOptions
     */
    public function __construct(
        public array $vehicleTypes,
        public array $activeOptions,
    ) {}

    public static function make(): self
    {
        return new self(
            vehicleTypes: VehicleType::options(),
            activeOptions: [
                ['value' => '', 'label' => __('common.all')],
                ['value' => '1', 'label' => __('common.active')],
                ['value' => '0', 'label' => __('common.inactive')],
            ],
        );
    }
}
