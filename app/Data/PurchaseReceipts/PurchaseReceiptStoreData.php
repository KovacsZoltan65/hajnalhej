<?php

declare(strict_types=1);

namespace App\Data\PurchaseReceipts;

use Spatie\LaravelData\Data;

class PurchaseReceiptStoreData extends Data
{
    // TODO: Kösd be a receipt admin flow-ba, amikor külön controller/service réteg készül hozzá.

    /**
     * @param  array<int, PurchaseReceiptItemData>  $items
     */
    public function __construct(
        public int $purchase_id,
        public string $receipt_number,
        public string $received_date,
        public string $status,
        public array $items,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            purchase_id: (int) $payload['purchase_id'],
            receipt_number: (string) $payload['receipt_number'],
            received_date: (string) $payload['received_date'],
            status: (string) $payload['status'],
            items: array_map(
                static fn (array $item): PurchaseReceiptItemData => PurchaseReceiptItemData::from($item),
                $payload['items'] ?? [],
            ),
            notes: isset($payload['notes']) ? (string) $payload['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'purchase_id' => $this->purchase_id,
            'receipt_number' => $this->receipt_number,
            'received_date' => $this->received_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'items' => array_map(
                static fn (PurchaseReceiptItemData $item): array => $item->toPayload(),
                $this->items,
            ),
        ];
    }
}
