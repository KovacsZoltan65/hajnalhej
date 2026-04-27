<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Branch;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;

class BranchData extends Data
{
    public function __construct(
        public ?int $id,

        #[Required, StringType]
        public string $name,

        #[Required, StringType]
        public string $code,

        #[Required, StringType]
        public string $type,
        public string $email,
        public string $phone,
        public string $address,

        #[BooleanType]
        public bool $active = true,
        public array $meta,
    ) {}

    public static function fromModel(Branch $branch): self
    {
        return new self(
            id: $branch->id,
            name: $branch->name,
            code: $branch->code,
            type: $branch->type,
            email: optional($branch->email, ""),
            phone: optional($branch->phone, ""),
            address: optional($branch->address, ""),
            active: $branch->active,
            meta: optional($branch->meta, [])
        );
    }
}