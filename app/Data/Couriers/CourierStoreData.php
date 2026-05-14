<?php

declare(strict_types=1);

namespace App\Data\Couriers;

use App\Models\Courier;
use Spatie\LaravelData\Data;

class CourierStoreData extends Data
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public string $name,
        public ?string $phone = null,
        public ?string $email = null,
        public string $status = Courier::STATUS_ACTIVE,
        public ?string $vehicle_type = null,
        public ?string $notes = null,
        public ?array $meta = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'name' => trim($this->name),
            'phone' => $this->nullableTrim($this->phone),
            'email' => $this->nullableTrim($this->email),
            'status' => $this->status,
            'vehicle_type' => $this->nullableTrim($this->vehicle_type),
            'active' => $this->status === Courier::STATUS_ACTIVE,
            'notes' => $this->nullableTrim($this->notes),
            'meta' => $this->meta,
        ];
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
