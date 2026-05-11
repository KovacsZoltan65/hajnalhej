<?php

declare(strict_types=1);

namespace App\Data\Couriers;

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
        public ?string $vehicle_type = null,
        public bool $active = true,
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
            'vehicle_type' => $this->nullableTrim($this->vehicle_type),
            'active' => $this->active,
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
