<?php

declare(strict_types=1);

namespace App\Data\Ingredients;

use Spatie\LaravelData\Data;

class IngredientUpdateData extends Data
{
    public function __construct(
        public string $name,
        public string $unit,
        public ?string $slug = null,
        public ?string $sku = null,
        public int|float|string|null $estimated_unit_cost = null,
        public int|float|string|null $current_stock = null,
        public int|float|string|null $minimum_stock = null,
        public bool $is_active = true,
        public ?string $notes = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $sku = trim((string) ($this->sku ?? ''));

        return [
            'name' => trim($this->name),
            'slug' => trim((string) ($this->slug ?? '')),
            'sku' => $sku !== '' ? $sku : null,
            'unit' => $this->unit,
            'estimated_unit_cost' => number_format((float) ($this->estimated_unit_cost ?? 0), 4, '.', ''),
            'current_stock' => number_format((float) ($this->current_stock ?? 0), 3, '.', ''),
            'minimum_stock' => number_format((float) ($this->minimum_stock ?? 0), 3, '.', ''),
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ];
    }
}
