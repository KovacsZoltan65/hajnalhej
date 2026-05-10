<?php

declare(strict_types=1);

namespace App\Data\Purchases;

use App\Models\PurchaseItem;
use Spatie\LaravelData\Data;

class PurchaseItemData extends Data
{
    public function __construct(
        public int $ingredient_id,
        public int|float|string $quantity,
        public string $unit,
        public int|float|string $unit_cost,
        public ?int $id = null,
        public ?string $ingredient_name = null,
        public ?string $ingredient_unit = null,
        public int|float|string|null $line_total = null,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            ingredient_id: (int) $payload['ingredient_id'],
            quantity: $payload['quantity'],
            unit: (string) $payload['unit'],
            unit_cost: $payload['unit_cost'],
            id: isset($payload['id']) ? (int) $payload['id'] : null,
            ingredient_name: isset($payload['ingredient_name']) ? (string) $payload['ingredient_name'] : null,
            ingredient_unit: isset($payload['ingredient_unit']) ? (string) $payload['ingredient_unit'] : null,
            line_total: $payload['line_total'] ?? null,
        );
    }

    public static function fromModel(PurchaseItem $item): self
    {
        return new self(
            ingredient_id: $item->ingredient_id,
            quantity: (float) $item->quantity,
            unit: $item->unit,
            unit_cost: (float) $item->unit_cost,
            id: $item->id,
            ingredient_name: $item->ingredient?->name,
            ingredient_unit: $item->ingredient?->unit,
            line_total: (float) $item->line_total,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'ingredient_id' => $this->ingredient_id,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'unit_cost' => $this->unit_cost,
        ];
    }
}
