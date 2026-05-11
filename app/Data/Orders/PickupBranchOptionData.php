<?php

declare(strict_types=1);

namespace App\Data\Orders;

use App\Models\Branch;
use Spatie\LaravelData\Data;

class PickupBranchOptionData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $code,
        public string $type,
        public ?string $address,
    ) {}

    public static function fromModel(Branch $branch): self
    {
        return new self(
            id: $branch->id,
            name: $branch->name,
            code: $branch->code,
            type: $branch->type,
            address: $branch->address,
        );
    }
}
