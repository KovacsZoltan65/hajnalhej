<?php

declare(strict_types=1);

namespace App\Data\PurchaseReceipts;

use App\Models\PurchaseReceiptItem;
use Spatie\LaravelData\Data;

class PurchaseReceiptItemData extends Data
{
    public function __construct(
        public int $ingredient_id,
        public int|float|string $ordered_quantity,
        public int|float|string $received_quantity,
        public int|float|string $rejected_quantity,
        public string $unit,
        public int|float|string $unit_cost,
        public string $quality_status,
        public ?int $purchase_item_id = null,
        public ?string $notes = null,
        public ?int $id = null,
        public ?string $ingredient_name = null,
        public int|float|string|null $line_total = null,
    ) {}

    public static function fromModel(PurchaseReceiptItem $item): self
    {
        return new self(
            ingredient_id: $item->ingredient_id,
            ordered_quantity: (float) $item->ordered_quantity,
            received_quantity: (float) $item->received_quantity,
            rejected_quantity: (float) $item->rejected_quantity,
            unit: $item->unit,
            unit_cost: (float) $item->unit_cost,
            quality_status: $item->quality_status,
            purchase_item_id: $item->purchase_item_id,
            notes: $item->notes,
            id: $item->id,
            ingredient_name: $item->ingredient?->name,
            line_total: (float) $item->line_total,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'purchase_item_id' => $this->purchase_item_id,
            'ingredient_id' => $this->ingredient_id,
            'ordered_quantity' => $this->ordered_quantity,
            'received_quantity' => $this->received_quantity,
            'rejected_quantity' => $this->rejected_quantity,
            'unit' => $this->unit,
            'unit_cost' => $this->unit_cost,
            'quality_status' => $this->quality_status,
            'notes' => $this->notes,
        ];
    }
}
