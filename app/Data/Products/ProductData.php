<?php

declare(strict_types=1);

namespace App\Data\Products;

use App\Models\Product;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public int $id,
        public int $category_id,
        public string $name,
        public string $slug,
        public ?string $short_description,
        public ?string $description,
        public float $price,
        public ?string $image_path,
        public int $sort_order,
        public bool $is_active,
        public bool $is_featured,
        public string $stock_status,
    ) {}

    /**
     * @param Product $product
     * @return ProductData
     */
    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            category_id: $product->category_id,
            name: $product->name,
            slug: (string) $product->slug,
            short_description: $product->short_description,
            description: $product->description,
            price: (float) $product->price,
            image_path: $product->image_path,
            sort_order: $product->sort_order,
            is_active: $product->is_active,
            is_featured: $product->is_featured,
            stock_status: $product->stock_status,
        );
    }
}
