<?php

declare(strict_types=1);

namespace App\Data\Purchases;

use App\Models\Purchase;
use Spatie\LaravelData\Data;

class PurchaseListItemData extends Data
{
    public function __construct(
        public int $id,
        public ?int $supplier_id,
        public ?string $supplier_name,
        public ?string $reference_number,
        public ?string $purchase_date,
        public string $status,
        public float $subtotal,
        public float $total,
        public ?string $notes,
        public int $items_count,
        public ?string $posted_at,
        public ?string $created_by,
    ) {}

    public static function fromModel(Purchase $purchase): self
    {
        return new self(
            id: $purchase->id,
            supplier_id: $purchase->supplier_id,
            supplier_name: $purchase->supplier?->name,
            reference_number: $purchase->reference_number,
            purchase_date: $purchase->purchase_date?->toDateString(),
            status: $purchase->status,
            subtotal: (float) $purchase->subtotal,
            total: (float) $purchase->total,
            notes: $purchase->notes,
            items_count: (int) ($purchase->items_count ?? 0),
            posted_at: $purchase->posted_at?->toDateTimeString(),
            created_by: $purchase->creator?->name,
        );
    }
}
