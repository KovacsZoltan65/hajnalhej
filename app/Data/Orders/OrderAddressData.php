<?php

declare(strict_types=1);

namespace App\Data\Orders;

use Spatie\LaravelData\Data;

class OrderAddressData extends Data
{
    public function __construct(
        public string $name,
        public string $country,
        public string $postal_code,
        public string $city,
        public string $street,
        public string $house_number,
        public ?string $floor = null,
        public ?string $door = null,
        public ?string $company_name = null,
        public ?string $tax_number = null,
        public ?string $phone = null,
        public ?string $notes = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toSnapshot(): array
    {
        return [
            'name' => trim($this->name),
            'country' => trim($this->country),
            'postal_code' => trim($this->postal_code),
            'city' => trim($this->city),
            'street' => trim($this->street),
            'house_number' => trim($this->house_number),
            'floor' => $this->nullableTrim($this->floor),
            'door' => $this->nullableTrim($this->door),
            'company_name' => $this->nullableTrim($this->company_name),
            'tax_number' => $this->nullableTrim($this->tax_number),
            'phone' => $this->nullableTrim($this->phone),
            'notes' => $this->nullableTrim($this->notes),
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
