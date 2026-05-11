<?php

declare(strict_types=1);

namespace App\Data\IngredientSupplierTerms;

use App\Models\IngredientSupplierTerm;
use Spatie\LaravelData\Data;

class IngredientSupplierTermListItemData extends Data
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public int $id,
        public int $ingredient_id,
        public int $supplier_id,
        public ?string $ingredient_name,
        public ?string $ingredient_unit,
        public ?string $supplier_name,
        public ?int $lead_time_days,
        public int|float|string|null $minimum_order_quantity,
        public int|float|string|null $pack_size,
        public int|float|string|null $unit_cost_override,
        public bool $preferred,
        public bool $active,
        public ?array $meta,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(IngredientSupplierTerm $term): self
    {
        return new self(
            id: $term->id,
            ingredient_id: $term->ingredient_id,
            supplier_id: $term->supplier_id,
            ingredient_name: $term->ingredient?->name,
            ingredient_unit: $term->ingredient?->unit,
            supplier_name: $term->supplier?->name,
            lead_time_days: $term->lead_time_days,
            minimum_order_quantity: $term->minimum_order_quantity,
            pack_size: $term->pack_size,
            unit_cost_override: $term->unit_cost_override,
            preferred: $term->preferred,
            active: $term->active,
            meta: $term->meta,
            created_at: $term->created_at?->toDateTimeString(),
            updated_at: $term->updated_at?->toDateTimeString(),
        );
    }
}
