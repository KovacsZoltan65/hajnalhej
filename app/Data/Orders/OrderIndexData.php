<?php

declare(strict_types=1);

namespace App\Data\Orders;

use App\Models\Order;
use Spatie\LaravelData\Data;

class OrderIndexData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public int $per_page = 15,
        public string $sort_field = 'placed_at',
        public string $sort_direction = 'desc',
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $status = self::nullableString($payload['status'] ?? null);

        if ($status !== null && ! in_array($status, Order::statuses(), true)) {
            $status = null;
        }

        return new self(
            search: self::nullableString($payload['search'] ?? null),
            status: $status,
            per_page: min(100, max(10, (int) ($payload['per_page'] ?? 15))),
            sort_field: self::allowedString(
                $payload['sort_field'] ?? null,
                ['placed_at', 'total', 'status', 'customer_name', 'pickup_date'],
                'placed_at',
            ),
            sort_direction: self::allowedString($payload['sort_direction'] ?? null, ['asc', 'desc'], 'desc'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontendFilters(): array
    {
        return [
            'search' => $this->search ?? '',
            'status' => $this->status ?? '',
            'sort_field' => $this->sort_field,
            'sort_direction' => $this->sort_direction,
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

    /**
     * @param  array<int, string>  $allowed
     */
    private static function allowedString(mixed $value, array $allowed, string $default): string
    {
        if (! is_string($value) || ! in_array($value, $allowed, true)) {
            return $default;
        }

        return $value;
    }
}
