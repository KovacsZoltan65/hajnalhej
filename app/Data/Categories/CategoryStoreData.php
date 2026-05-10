<?php

declare(strict_types=1);

namespace App\Data\Categories;

use Spatie\LaravelData\Data;

class CategoryStoreData extends Data
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $description = null,
        public bool $is_active = true,
        public int $sort_order = 0,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'name' => trim($this->name),
            'slug' => trim((string) ($this->slug ?? '')),
            'description' => $this->description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
