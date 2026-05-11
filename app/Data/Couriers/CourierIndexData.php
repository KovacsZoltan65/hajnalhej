<?php

declare(strict_types=1);

namespace App\Data\Couriers;

use App\Enums\Delivery\VehicleType;
use Spatie\LaravelData\Data;

class CourierIndexData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $vehicle_type = null,
        public ?bool $active = null,
        public int $page = 1,
        public int $per_page = 10,
        public string $sort_field = 'name',
        public string $sort_direction = 'asc',
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            search: self::nullableString($payload['search'] ?? null),
            vehicle_type: self::allowedNullableString($payload['vehicle_type'] ?? null, VehicleType::values()),
            active: self::nullableBoolean($payload['active'] ?? null),
            page: max(1, (int) ($payload['page'] ?? 1)),
            per_page: min(50, max(5, (int) ($payload['per_page'] ?? 10))),
            sort_field: self::allowedString(
                $payload['sort_field'] ?? null,
                ['name', 'email', 'phone', 'vehicle_type', 'active', 'updated_at'],
                'name',
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
            'vehicle_type' => $this->vehicle_type ?? '',
            'active' => $this->active === null ? '' : ($this->active ? '1' : '0'),
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
    private static function allowedNullableString(mixed $value, array $allowed): ?string
    {
        if (! is_string($value) || $value === '' || ! in_array($value, $allowed, true)) {
            return null;
        }

        return $value;
    }

    private static function nullableBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
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
