<?php

declare(strict_types=1);

namespace App\Data\Purchases;

use Spatie\LaravelData\Data;

class PurchaseStoreData extends Data
{
    /**
     * @param  array<int, PurchaseItemData>  $items
     */
    public function __construct(
        public string $purchase_date,
        public array $items,
        public ?int $supplier_id = null,
        public ?string $reference_number = null,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            purchase_date: (string) $payload['purchase_date'],
            items: array_map(
                static fn (array $item): PurchaseItemData => PurchaseItemData::from($item),
                $payload['items'] ?? [],
            ),
            supplier_id: isset($payload['supplier_id']) ? (int) $payload['supplier_id'] : null,
            reference_number: isset($payload['reference_number']) ? (string) $payload['reference_number'] : null,
            notes: isset($payload['notes']) ? (string) $payload['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'supplier_id' => $this->supplier_id,
            'reference_number' => $this->reference_number,
            'purchase_date' => $this->purchase_date,
            'notes' => $this->notes,
            'items' => array_map(
                static fn (PurchaseItemData $item): array => $item->toPayload(),
                $this->items,
            ),
        ];
    }
}
