<?php

declare(strict_types=1);

namespace App\Data\Suppliers;

use App\Models\Supplier;
use Spatie\LaravelData\Data;

class SupplierListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $tax_number,
        public ?int $lead_time_days,
        public bool $active,
        public ?string $notes,
        public int $purchases_count,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(Supplier $supplier): self
    {
        return new self(
            id: $supplier->id,
            name: $supplier->name,
            email: $supplier->email,
            phone: $supplier->phone,
            tax_number: $supplier->tax_number,
            lead_time_days: $supplier->lead_time_days,
            active: $supplier->active,
            notes: $supplier->notes,
            purchases_count: (int) ($supplier->purchases_count ?? 0),
            created_at: $supplier->created_at?->toDateTimeString(),
            updated_at: $supplier->updated_at?->toDateTimeString(),
        );
    }
}
