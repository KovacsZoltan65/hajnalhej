<?php

declare(strict_types=1);

namespace App\Data\StockCounts;

use App\Models\StockCountItem;
use Spatie\LaravelData\Data;

class StockCountItemData extends Data
{
    public function __construct(
        public ?int $id,
        public int $ingredient_id,
        public ?string $ingredient_name,
        public ?string $unit,
        public float $expected_quantity,
        public float $counted_quantity,
        public float $difference,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $expected = round((float) ($payload['expected_quantity'] ?? 0), 3);
        $counted = round((float) ($payload['counted_quantity'] ?? 0), 3);

        return new self(
            id: isset($payload['id']) ? (int) $payload['id'] : null,
            ingredient_id: (int) ($payload['ingredient_id'] ?? 0),
            ingredient_name: self::nullableString($payload['ingredient_name'] ?? null),
            unit: self::nullableString($payload['unit'] ?? null),
            expected_quantity: $expected,
            counted_quantity: $counted,
            difference: round((float) ($payload['difference'] ?? ($counted - $expected)), 3),
        );
    }

    public static function fromModel(StockCountItem $item): self
    {
        return new self(
            id: $item->id,
            ingredient_id: $item->ingredient_id,
            ingredient_name: $item->ingredient?->name,
            unit: $item->ingredient?->unit,
            expected_quantity: (float) $item->expected_quantity,
            counted_quantity: (float) $item->counted_quantity,
            difference: (float) $item->difference,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toInputPayload(): array
    {
        return [
            'ingredient_id' => $this->ingredient_id,
            'expected_quantity' => $this->expected_quantity,
            'counted_quantity' => $this->counted_quantity,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
