<?php

declare(strict_types=1);

namespace App\Data\StockCounts;

use App\Models\StockCount;
use Spatie\LaravelData\Data;

class StockCountIndexData extends Data
{
    public function __construct(
        public ?string $status = null,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public int $per_page = 10,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $status = self::nullableString($payload['status'] ?? null);

        if ($status !== null && ! in_array($status, StockCount::statuses(), true)) {
            $status = null;
        }

        return new self(
            status: $status,
            date_from: self::nullableString($payload['date_from'] ?? null),
            date_to: self::nullableString($payload['date_to'] ?? null),
            per_page: min(50, max(5, (int) ($payload['per_page'] ?? 10))),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontendFilters(): array
    {
        return [
            'status' => $this->status ?? '',
            'date_from' => $this->date_from ?? '',
            'date_to' => $this->date_to ?? '',
            'per_page' => $this->per_page,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
