<?php

declare(strict_types=1);

namespace App\Data\StockCounts;

use Spatie\LaravelData\Data;

class StockCountStoreData extends Data
{
    /**
     * @param  array<int, StockCountItemData>  $items
     */
    public function __construct(
        public string $count_date,
        public ?string $notes,
        public array $items,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            count_date: (string) $payload['count_date'],
            notes: self::stringOrNull($payload['notes'] ?? null),
            items: array_map(
                static fn (array $item): StockCountItemData => StockCountItemData::from($item),
                $payload['items'] ?? [],
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'count_date' => $this->count_date,
            'notes' => $this->notes,
            'items' => array_map(
                static fn (StockCountItemData $item): array => $item->toInputPayload(),
                $this->items,
            ),
        ];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
