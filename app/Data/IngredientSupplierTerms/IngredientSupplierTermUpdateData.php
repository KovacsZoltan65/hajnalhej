<?php

declare(strict_types=1);

namespace App\Data\IngredientSupplierTerms;

use Spatie\LaravelData\Data;

class IngredientSupplierTermUpdateData extends Data
{
    public function __construct(
        public int $ingredient_id,
        public int $supplier_id,
        public ?int $lead_time_days = null,
        public int|float|string|null $minimum_order_quantity = null,
        public int|float|string|null $pack_size = null,
        public int|float|string|null $unit_cost_override = null,
        public bool $preferred = false,
        public bool $active = true,
        public string|array|null $meta = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $active = $this->active;

        return [
            'ingredient_id' => $this->ingredient_id,
            'supplier_id' => $this->supplier_id,
            'lead_time_days' => $this->lead_time_days,
            'minimum_order_quantity' => self::nullableDecimal($this->minimum_order_quantity),
            'pack_size' => self::nullableDecimal($this->pack_size),
            'unit_cost_override' => self::nullableDecimal($this->unit_cost_override),
            'preferred' => $active && $this->preferred,
            'active' => $active,
            'meta' => self::normalizeMeta($this->meta),
        ];
    }

    private static function nullableDecimal(int|float|string|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function normalizeMeta(string|array|null $value): ?array
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
