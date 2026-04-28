<?php

declare(strict_types=1);

namespace App\Data\Products;

use App\Models\Product;
use Spatie\LaravelData\Data;

class ProductStoreData extends Data
{
    public function __construct(
        public int $category_id,
        public string $name,
        public int|float $price,
        public ?string $slug = null,
        public ?string $short_description = null,
        public ?string $description = null,
        public ?string $image_path = null,
        public int $sort_order = 0,
        public bool $is_active = true,
        public bool $is_featured = false,
        public string $stock_status = Product::STOCK_IN_STOCK,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'price' => number_format((float) $this->price, 2, '.', ''),
            'image_path' => $this->image_path,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'stock_status' => $this->stock_status,
        ];
    }
}
