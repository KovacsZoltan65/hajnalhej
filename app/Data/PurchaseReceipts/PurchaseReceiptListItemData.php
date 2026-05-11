<?php

declare(strict_types=1);

namespace App\Data\PurchaseReceipts;

use App\Models\PurchaseReceipt;
use Spatie\LaravelData\Data;

class PurchaseReceiptListItemData extends Data
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public int $id,
        public int $purchase_id,
        public string $receipt_number,
        public ?string $received_date,
        public string $status,
        public float $total_received_value,
        public ?string $notes,
        public ?string $received_by,
        public ?string $posted_at,
        public array $items = [],
    ) {}

    public static function fromModel(PurchaseReceipt $receipt): self
    {
        return new self(
            id: $receipt->id,
            purchase_id: $receipt->purchase_id,
            receipt_number: $receipt->receipt_number,
            received_date: $receipt->received_date?->toDateString(),
            status: $receipt->status,
            total_received_value: (float) $receipt->total_received_value,
            notes: $receipt->notes,
            received_by: $receipt->receiver?->name,
            posted_at: $receipt->posted_at?->toDateTimeString(),
            items: $receipt->items
                ->map(fn ($item): array => PurchaseReceiptItemData::from($item)->toArray())
                ->values()
                ->all(),
        );
    }
}
