<?php

declare(strict_types=1);

namespace App\Data\Categories;

use Spatie\LaravelData\Data;

class CategoryIndexData extends Data
{
    public function __construct(
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 10,
        public string $sort_field = 'sort_order',
        public string $sort_direction = 'asc',
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            search: self::nullableString($payload['search'] ?? null),
            page: max(1, (int) ($payload['page'] ?? 1)),
            per_page: min(50, max(5, (int) ($payload['per_page'] ?? 10))),
            sort_field: self::allowedString($payload['sort_field'] ?? null, ['name', 'sort_order', 'is_active'], 'sort_order'),
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
