<?php

declare(strict_types=1);

namespace App\Data\IngredientSupplierTerms;

use Spatie\LaravelData\Data;

class IngredientSupplierTermIndexData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?bool $active = null,
        public int $page = 1,
        public int $per_page = 10,
        public string $sort_field = 'ingredient',
        public string $sort_direction = 'asc',
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            search: self::nullableString($payload['search'] ?? null),
            active: self::nullableBool($payload['active'] ?? null),
            page: max(1, (int) ($payload['page'] ?? 1)),
            per_page: min(100, max(5, (int) ($payload['per_page'] ?? 10))),
            sort_field: self::allowedString(
                $payload['sort_field'] ?? null,
                ['ingredient', 'supplier', 'lead_time_days', 'minimum_order_quantity', 'pack_size', 'unit_cost_override', 'active', 'preferred'],
                'ingredient',
            ),
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
            'active' => $this->active === null ? '' : (string) (int) $this->active,
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
