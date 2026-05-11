<?php

declare(strict_types=1);

namespace App\Data\StockCounts;

use App\Models\StockCount;
use Spatie\LaravelData\Data;

class StockCountDetailData extends Data
{
    /**
     * @param  array<int, StockCountItemData>  $items
     */
    public function __construct(
        public int $id,
        public ?string $count_date,
        public string $status,
        public ?string $notes,
        public ?string $closed_at,
        public array $items,
    ) {}

    public static function fromModel(StockCount $stockCount): self
    {
        return new self(
            id: $stockCount->id,
            count_date: $stockCount->count_date?->toDateString(),
            status: $stockCount->status,
            notes: $stockCount->notes,
            closed_at: $stockCount->closed_at?->toDateTimeString(),
            items: $stockCount->items
                ->map(fn ($item): StockCountItemData => StockCountItemData::fromModel($item))
                ->values()
                ->all(),
        );
    }
}
