<?php

declare(strict_types=1);

namespace App\Data\WeeklyMenu;

use App\Models\WeeklyMenu;
use Spatie\LaravelData\Data;

class WeeklyMenuIndexData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?bool $active = null,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public int $page = 1,
        public int $per_page = 10,
        public string $sort_field = 'week_start',
        public string $sort_direction = 'desc',
        public ?string $status = null,
    ) {}

    /**
     * @param array $payload
     * @return WeeklyMenuIndexData
     */
    public static function fromArray(array $payload): self
    {
        $active = self::nullableBool($payload['active'] ?? $payload['is_active'] ?? null);
        $status = self::nullableString($payload['status'] ?? null);

        if ($active !== null) {
            $status = $active ? WeeklyMenu::STATUS_PUBLISHED : WeeklyMenu::STATUS_DRAFT;
        }

        if ($status !== null && ! \in_array($status, WeeklyMenu::statuses(), true)) {
            $status = null;
        }

        return new self(
            search: self::nullableString($payload['search'] ?? null),
            active: $active,
            date_from: self::nullableString($payload['date_from'] ?? $payload['start_date'] ?? null),
            date_to: self::nullableString($payload['date_to'] ?? $payload['end_date'] ?? null),
            page: max(1, (int) ($payload['page'] ?? 1)),
            per_page: min(50, max(5, (int) ($payload['per_page'] ?? 10))),
            sort_field: self::allowedString($payload['sort_field'] ?? null, ['week_start', 'week_end', 'status', 'title'], 'week_start'),
            sort_direction: self::allowedString($payload['sort_direction'] ?? null, ['asc', 'desc'], 'desc'),
            status: $status,
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
            'active' => $this->active,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'sort_field' => $this->sort_field,
            'sort_direction' => $this->sort_direction,
            'per_page' => $this->per_page,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! \is_string($value)) {
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
        if (! \is_string($value) || ! \in_array($value, $allowed, true)) {
            return $default;
        }

        return $value;
    }
}
