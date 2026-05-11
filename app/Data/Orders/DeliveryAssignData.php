<?php

declare(strict_types=1);

namespace App\Data\Orders;

use Spatie\LaravelData\Data;

class DeliveryAssignData extends Data
{
    public function __construct(
        public int $courier_id,
        public ?string $delivery_scheduled_at = null,
    ) {}
}
