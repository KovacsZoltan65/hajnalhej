<?php

declare(strict_types=1);

namespace App\Data\Orders;

use Spatie\LaravelData\Data;

class OrderStatusUpdateData extends Data
{
    public function __construct(
        public string $status,
        public ?string $internal_notes = null,
        public ?string $pickup_date = null,
        public ?string $pickup_time_slot = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            status: (string) $payload['status'],
            internal_notes: self::stringOrNull($payload['internal_notes'] ?? null),
            pickup_date: self::stringOrNull($payload['pickup_date'] ?? null),
            pickup_time_slot: self::stringOrNull($payload['pickup_time_slot'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'status' => $this->status,
            'internal_notes' => $this->internal_notes,
            'pickup_date' => $this->pickup_date,
            'pickup_time_slot' => $this->pickup_time_slot,
        ];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
