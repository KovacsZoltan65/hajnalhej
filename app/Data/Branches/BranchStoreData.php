<?php

declare(strict_types=1);

namespace App\Data\Branches;

use Spatie\LaravelData\Data;

class BranchStoreData extends Data
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public string $name,
        public string $code,
        public string $type,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public bool $active = true,
        public ?array $meta = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'name' => trim($this->name),
            'code' => mb_strtoupper(trim($this->code)),
            'type' => $this->type,
            'email' => $this->nullableTrim($this->email),
            'phone' => $this->nullableTrim($this->phone),
            'address' => $this->nullableTrim($this->address),
            'active' => $this->active,
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
