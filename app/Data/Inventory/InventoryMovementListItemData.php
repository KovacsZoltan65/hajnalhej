<?php

declare(strict_types=1);

namespace App\Data\Inventory;

use App\Models\InventoryMovement;
use Spatie\LaravelData\Data;

class InventoryMovementListItemData extends Data
{
    public function __construct(
        public int $id,
        public int $ingredient_id,
        public ?string $ingredient_name,
        public ?string $ingredient_unit,
        public string $movement_type,
        public string $direction,
        public float $quantity,
        public ?float $unit_cost,
        public ?float $total_cost,
        public ?string $occurred_at,
        public ?string $reference_type,
        public ?int $reference_id,
        public ?string $notes,
        public ?string $created_by,
    ) {}

    public static function fromModel(InventoryMovement $movement): self
    {
        return new self(
            id: $movement->id,
            ingredient_id: $movement->ingredient_id,
            ingredient_name: $movement->ingredient?->name,
            ingredient_unit: $movement->ingredient?->unit,
            movement_type: $movement->movement_type,
            direction: $movement->direction,
            quantity: (float) $movement->quantity,
            unit_cost: $movement->unit_cost !== null ? (float) $movement->unit_cost : null,
            total_cost: $movement->total_cost !== null ? (float) $movement->total_cost : null,
            occurred_at: $movement->occurred_at?->toDateTimeString(),
            reference_type: $movement->reference_type,
            reference_id: $movement->reference_id,
            notes: $movement->notes,
            created_by: $movement->creator?->name,
        );
    }
}
