<?php

declare(strict_types=1);

namespace App\Data\Inventory;

use App\Models\InventoryMovement;
use Spatie\LaravelData\Data;

class InventoryLedgerIndexData extends Data
{
    public function __construct(
        public int $days = 7,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public ?int $ingredient_id = null,
        public ?string $movement_type = null,
        public ?string $search = null,
        public int $per_page = 15,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $movementType = self::nullableString($payload['movement_type'] ?? null);

        if ($movementType !== null && ! in_array($movementType, InventoryMovement::movementTypes(), true)) {
            $movementType = null;
        }

        return new self(
            days: self::allowedInt((int) ($payload['days'] ?? 7), [7, 14, 30, 90], 7),
            date_from: self::nullableString($payload['date_from'] ?? null),
            date_to: self::nullableString($payload['date_to'] ?? null),
            ingredient_id: self::nullableInt($payload['ingredient_id'] ?? null),
            movement_type: $movementType,
            search: self::nullableString($payload['search'] ?? null),
            per_page: min(100, max(5, (int) ($payload['per_page'] ?? 15))),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontendFilters(): array
    {
        return [
            'days' => $this->days,
            'date_from' => $this->date_from ?? '',
            'date_to' => $this->date_to ?? '',
            'ingredient_id' => $this->ingredient_id ?? '',
            'movement_type' => $this->movement_type ?? '',
            'search' => $this->search ?? '',
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

        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    /**
     * @param  array<int, int>  $allowed
     */
    private static function allowedInt(int $value, array $allowed, int $default): int
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
