<?php

declare(strict_types=1);

namespace App\Data\Orders;

use Spatie\LaravelData\Data;

class DeliveryFailedData extends Data
{
    public function __construct(
        public string $failed_delivery_reason,
    ) {}

    public function reason(): string
    {
        return trim($this->failed_delivery_reason);
    }
}
