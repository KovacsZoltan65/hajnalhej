<?php

declare(strict_types=1);

namespace App\Data\Branches;

use App\Models\Branch;
use Spatie\LaravelData\Data;

class BranchListItemData extends Data
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $code,
        public string $type,
        public string $type_label,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public bool $active,
        public ?array $meta,
        public ?string $updated_at,
    ) {}

    public static function fromModel(Branch $branch): self
    {
        return new self(
            id: $branch->id,
            name: $branch->name,
            code: $branch->code,
            type: $branch->type,
            type_label: __("admin_branches.types.{$branch->type}"),
            email: $branch->email,
            phone: $branch->phone,
            address: $branch->address,
            active: $branch->active,
            meta: $branch->meta,
            updated_at: $branch->updated_at?->toDateTimeString(),
        );
    }
}
