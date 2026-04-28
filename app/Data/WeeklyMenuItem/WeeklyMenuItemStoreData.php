<?php

declare(strict_types=1);

namespace App\Data\WeeklyMenuItem;

use Spatie\LaravelData\Data;

class WeeklyMenuItemStoreData extends Data
{
    public function __construct(
        public int $product_id,
        public ?float $price_override = null,
        public bool $is_available = true,
        public int $sort_order = 0,
        public ?string $override_name = null,
        public ?string $override_short_description = null,
        public ?string $badge_text = null,
        public ?string $stock_note = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'product_id' => $this->product_id,
            'override_name' => $this->override_name,
            'override_short_description' => $this->override_short_description,
            'override_price' => $this->price_override !== null ? number_format($this->price_override, 2, '.', '') : null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_available,
            'badge_text' => $this->badge_text,
            'stock_note' => $this->stock_note,
        ];
    }
}
