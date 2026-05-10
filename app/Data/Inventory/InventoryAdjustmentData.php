<?php

declare(strict_types=1);

namespace App\Data\Inventory;

use Spatie\LaravelData\Data;

class InventoryAdjustmentData extends Data
{
    public function __construct(
        public int $ingredient_id,
        public float $difference,
        public ?float $unit_cost = null,
        public ?string $occurred_at = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            ingredient_id: (int) $payload['ingredient_id'],
            difference: round((float) $payload['difference'], 3),
            unit_cost: isset($payload['unit_cost']) && $payload['unit_cost'] !== '' ? round((float) $payload['unit_cost'], 4) : null,
            occurred_at: self::stringOrNull($payload['occurred_at'] ?? null),
            notes: self::stringOrNull($payload['notes'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'ingredient_id' => $this->ingredient_id,
            'difference' => $this->difference,
            'unit_cost' => $this->unit_cost,
            'occurred_at' => $this->occurred_at,
            'notes' => $this->notes,
        ];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
