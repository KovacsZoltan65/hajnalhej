<?php

declare(strict_types=1);

namespace App\Data\Purchases;

use App\Models\Purchase;
use Spatie\LaravelData\Data;

class PurchaseIndexData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?int $supplier_id = null,
        public int $page = 1,
        public int $per_page = 10,
        public string $sort_field = 'purchase_date',
        public string $sort_direction = 'desc',
    ) {}

    public static function fromArray(array $payload): self
    {
        $status = self::nullableString($payload['status'] ?? null);

        if ($status !== null && ! in_array($status, Purchase::statuses(), true)) {
            $status = null;
        }

        return new self(
            search: self::nullableString($payload['search'] ?? null),
            status: $status,
            supplier_id: self::nullableInt($payload['supplier_id'] ?? null),
            page: max(1, (int) ($payload['page'] ?? 1)),
            per_page: min(50, max(5, (int) ($payload['per_page'] ?? 10))),
            sort_field: self::allowedString($payload['sort_field'] ?? null, ['purchase_date', 'total', 'status', 'created_at'], 'purchase_date'),
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
            'supplier_id' => $this->supplier_id ?? '',
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

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
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
