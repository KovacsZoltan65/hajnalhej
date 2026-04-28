<?php

declare(strict_types=1);

namespace App\Data\Products;

use Spatie\LaravelData\Data;

class ProductIndexData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?int $category_id = null,
        public ?bool $active = null,
        public int $page = 1,
        public int $per_page = 10,
        public string $sort_field = 'sort_order',
        public string $sort_direction = 'asc',
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            search: self::nullableString($payload['search'] ?? null),
            category_id: self::nullableInt($payload['category_id'] ?? null),
            active: self::nullableBool($payload['active'] ?? $payload['is_active'] ?? null),
            page: max(1, (int) ($payload['page'] ?? 1)),
            per_page: min(50, max(5, (int) ($payload['per_page'] ?? 10))),
            sort_field: self::allowedString($payload['sort_field'] ?? null, ['name', 'slug', 'price', 'is_active', 'sort_order'], 'sort_order'),
            sort_direction: self::allowedString($payload['sort_direction'] ?? null, ['asc', 'desc'], 'asc'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontendFilters(): array
    {
        return [
            'search' => $this->search ?? '',
            'category_id' => $this->category_id,
            'is_active' => $this->active === null ? '' : (string) (int) $this->active,
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

    private static function nullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
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
