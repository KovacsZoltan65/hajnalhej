<?php

declare(strict_types=1);

namespace App\Data\WeeklyMenu;

use App\Models\WeeklyMenu;
use Spatie\LaravelData\Data;

class WeeklyMenuStoreData extends Data
{
    public function __construct(
        public string $name,
        public string $start_date,
        public string $end_date,
        public bool $is_active = false,
        public ?string $slug = null,
        public ?string $public_note = null,
        public ?string $internal_note = null,
        public bool $is_featured = false,
        public string $status = WeeklyMenu::STATUS_DRAFT,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $status = $this->is_active ? WeeklyMenu::STATUS_PUBLISHED : $this->status;

        return [
            'title' => $this->name,
            'slug' => $this->slug,
            'week_start' => $this->start_date,
            'week_end' => $this->end_date,
            'status' => $status,
            'public_note' => $this->public_note,
            'internal_note' => $this->internal_note,
            'is_featured' => $this->is_featured,
        ];
    }
}
