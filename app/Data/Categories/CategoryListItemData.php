<?php

declare(strict_types=1);

namespace App\Data\Categories;

use App\Models\Category;
use Spatie\LaravelData\Data;

class CategoryListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public bool $is_active,
        public int $sort_order,
        public int $products_count,
        public ?string $updated_at,
    ) {}

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
            description: $category->description,
            is_active: $category->is_active,
            sort_order: $category->sort_order,
            products_count: (int) ($category->products_count ?? 0),
            updated_at: $category->updated_at?->toDateTimeString(),
        );
    }
}
