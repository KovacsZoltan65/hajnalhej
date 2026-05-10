<?php

declare(strict_types=1);

namespace App\Data\Suppliers;

use Spatie\LaravelData\Data;

class SupplierStoreData extends Data
{
    public function __construct(
        public string $name,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $tax_number = null,
        public int|string|null $lead_time_days = null,
        public ?string $notes = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'name' => trim($this->name),
            'email' => self::emptyToNull($this->email),
            'phone' => self::emptyToNull($this->phone),
            'tax_number' => self::emptyToNull($this->tax_number),
            'lead_time_days' => self::nullableInteger($this->lead_time_days),
            'notes' => self::emptyToNull($this->notes),
        ];
    }

    private static function emptyToNull(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private static function nullableInteger(int|string|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
