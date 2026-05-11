<?php

declare(strict_types=1);

namespace App\Data\Ingredients;

use App\Models\Ingredient;
use Spatie\LaravelData\Data;

class IngredientIndexData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?bool $is_active = null,
        public ?string $unit = null,
        public int $page = 1,
        public int $per_page = 10,
        public string $sort_field = 'name',
        public string $sort_direction = 'asc',
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            search: self::nullableString($payload['search'] ?? null),
            is_active: self::nullableBool($payload['is_active'] ?? null),
            unit: self::allowedNullableString($payload['unit'] ?? null, Ingredient::allowedUnits()),
            page: max(1, (int) ($payload['page'] ?? 1)),
            per_page: min(50, max(5, (int) ($payload['per_page'] ?? 10))),
            sort_field: self::allowedString($payload['sort_field'] ?? null, ['name', 'unit', 'estimated_unit_cost', 'current_stock', 'minimum_stock', 'is_active'], 'name'),
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
            'is_active' => $this->is_active === null ? '' : (string) (int) $this->is_active,
            'unit' => $this->unit ?? '',
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
    private static function allowedNullableString(mixed $value, array $allowed): ?string
    {
        if (! is_string($value) || ! in_array($value, $allowed, true)) {
            return null;
        }

        return $value;
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
