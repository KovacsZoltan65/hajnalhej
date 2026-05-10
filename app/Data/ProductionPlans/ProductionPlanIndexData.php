<?php

declare(strict_types=1);

namespace App\Data\ProductionPlans;

use App\Models\ProductionPlan;
use Spatie\LaravelData\Data;

class ProductionPlanIndexData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $target_from = null,
        public ?string $target_to = null,
        public int $page = 1,
        public int $per_page = 10,
        public string $sort_field = 'target_at',
        public string $sort_direction = 'asc',
    ) {}

    public static function fromArray(array $payload): self
    {
        $status = self::nullableString($payload['status'] ?? null);

        if ($status !== null && ! in_array($status, ProductionPlan::statuses(), true)) {
            $status = null;
        }

        return new self(
            search: self::nullableString($payload['search'] ?? null),
            status: $status,
            target_from: self::nullableString($payload['target_from'] ?? null),
            target_to: self::nullableString($payload['target_to'] ?? null),
            page: max(1, (int) ($payload['page'] ?? 1)),
            per_page: min(50, max(5, (int) ($payload['per_page'] ?? 10))),
            sort_field: self::allowedString(
                $payload['sort_field'] ?? null,
                ['plan_number', 'target_at', 'status', 'total_active_minutes', 'total_wait_minutes', 'total_recipe_minutes', 'planned_start_at', 'created_at'],
                'target_at',
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
            'status' => $this->status ?? '',
            'target_from' => $this->target_from,
            'target_to' => $this->target_to,
            'sort_field' => $this->sort_field,
            'sort_direction' => $this->sort_direction,
            'per_page' => $this->per_page,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
            'target_from' => $this->target_from,
            'target_to' => $this->target_to,
            'page' => $this->page,
            'per_page' => $this->per_page,
            'sort_field' => $this->sort_field,
            'sort_direction' => $this->sort_direction,
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
