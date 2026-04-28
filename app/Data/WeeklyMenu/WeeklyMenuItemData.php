<?php

declare(strict_types=1);

namespace App\Data\WeeklyMenu;

use App\Models\WeeklyMenuItem;
use Spatie\LaravelData\Data;

class WeeklyMenuItemData extends Data
{
    public function __construct(
        public int $id,
        public int $weekly_menu_id,
        public int $product_id,
        public ?string $product_name,
        public ?string $category_name,
        public ?string $override_name,
        public ?string $override_short_description,
        public ?float $override_price,
        public ?float $price_override,
        public float $price,
        public int $sort_order,
        public bool $is_active,
        public bool $is_available,
        public ?string $badge_text,
        public ?string $stock_note,
    ) {}

    public static function fromModel(WeeklyMenuItem $item): self
    {
        $overridePrice = $item->override_price !== null ? (float) $item->override_price : null;
        $productPrice = (float) ($item->product?->price ?? 0);

        return new self(
            id: $item->id,
            weekly_menu_id: $item->weekly_menu_id,
            product_id: $item->product_id,
            product_name: $item->product?->name,
            category_name: $item->product?->category?->name,
            override_name: $item->override_name,
            override_short_description: $item->override_short_description,
            override_price: $overridePrice,
            price_override: $overridePrice,
            price: $overridePrice ?? $productPrice,
            sort_order: $item->sort_order,
            is_active: $item->is_active,
            is_available: $item->is_active,
            badge_text: $item->badge_text,
            stock_note: $item->stock_note,
        );
    }
}
