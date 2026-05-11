<?php

declare(strict_types=1);

namespace App\Data\Ingredients;

use App\Models\Ingredient;
use Spatie\LaravelData\Data;

class IngredientListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $sku,
        public string $unit,
        public float $estimated_unit_cost,
        public float $current_stock,
        public float $minimum_stock,
        public bool $is_low_stock,
        public bool $is_active,
        public ?string $notes,
        public ?string $updated_at,
    ) {}

    public static function fromModel(Ingredient $ingredient): self
    {
        return new self(
            id: $ingredient->id,
            name: $ingredient->name,
            slug: $ingredient->slug,
            sku: $ingredient->sku,
            unit: $ingredient->unit,
            estimated_unit_cost: (float) $ingredient->estimated_unit_cost,
            current_stock: (float) $ingredient->current_stock,
            minimum_stock: (float) $ingredient->minimum_stock,
            is_low_stock: $ingredient->isLowStock(),
            is_active: $ingredient->is_active,
            notes: $ingredient->notes,
            updated_at: $ingredient->updated_at?->toDateTimeString(),
        );
    }
}
