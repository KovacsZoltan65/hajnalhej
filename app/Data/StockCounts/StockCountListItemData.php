<?php

declare(strict_types=1);

namespace App\Data\StockCounts;

use App\Models\StockCount;
use Spatie\LaravelData\Data;

class StockCountListItemData extends Data
{
    public function __construct(
        public int $id,
        public ?string $count_date,
        public string $status,
        public ?string $notes,
        public int $items_count,
        public ?string $created_by,
        public ?string $closed_at,
    ) {}

    public static function fromModel(StockCount $count): self
    {
        return new self(
            id: $count->id,
            count_date: $count->count_date?->toDateString(),
            status: $count->status,
            notes: $count->notes,
            items_count: (int) ($count->items_count ?? 0),
            created_by: $count->creator?->name,
            closed_at: $count->closed_at?->toDateTimeString(),
        );
    }
}
