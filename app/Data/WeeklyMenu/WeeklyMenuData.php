<?php

declare(strict_types=1);

namespace App\Data\WeeklyMenu;

use App\Models\WeeklyMenu;
use Spatie\LaravelData\Data;

class WeeklyMenuData extends Data
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $title,
        public string $slug,
        public ?string $start_date,
        public ?string $end_date,
        public ?string $week_start,
        public ?string $week_end,
        public bool $is_active,
        public string $status,
        public ?string $public_note,
        public ?string $internal_note,
        public bool $is_featured,
        public ?string $published_at,
        public array $items = [],
    ) {}

    /**
     * @param WeeklyMenu $menu
     * @return WeeklyMenuData
     */
    public static function fromModel(WeeklyMenu $menu): self
    {
        $weekStart = $menu->week_start?->toDateString();
        $weekEnd = $menu->week_end?->toDateString();

        return new self(
            id: $menu->id,
            name: $menu->title,
            title: $menu->title,
            slug: $menu->slug,
            start_date: $weekStart,
            end_date: $weekEnd,
            week_start: $weekStart,
            week_end: $weekEnd,
            is_active: $menu->status === WeeklyMenu::STATUS_PUBLISHED,
            status: $menu->status,
            public_note: $menu->public_note,
            internal_note: $menu->internal_note,
            is_featured: $menu->is_featured,
            published_at: $menu->published_at?->toDateTimeString(),
            items: $menu->items
                ->map(fn ($item): array => WeeklyMenuItemData::from($item)->toArray())
                ->values()
                ->all(),
        );
    }
}
