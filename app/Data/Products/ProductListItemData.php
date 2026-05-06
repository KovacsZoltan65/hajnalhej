<?php

declare(strict_types=1);

namespace App\Data\Products;

use App\Models\Product;
use App\Models\ProductIngredient;
use Spatie\LaravelData\Data;

class ProductListItemData extends Data
{
    /**
     * @param  array<int, array<string, mixed>>  $product_ingredients
     */
    public function __construct(
        public int $id,
        public ?int $category_id,
        public ?string $category_name,
        public string $name,
        public string $slug,
        public ?string $short_description,
        public ?string $description,
        public float $price,
        public bool $is_active,
        public bool $is_featured,
        public string $stock_status,
        public ?string $image_path,
        public int $sort_order,
        public array $product_ingredients = [],
        public ?string $updated_at = null,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            category_id: $product->category_id,
            category_name: $product->category?->name,
            name: $product->name,
            slug: (string) $product->slug,
            short_description: $product->short_description,
            description: $product->description,
            price: (float) $product->price,
            is_active: $product->is_active,
            is_featured: $product->is_featured,
            stock_status: $product->stock_status,
            image_path: $product->image_path,
            sort_order: $product->sort_order,
            product_ingredients: $product->productIngredients
                ->map(fn (ProductIngredient $item): array => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'ingredient_id' => $item->ingredient_id,
                    'ingredient_name' => $item->ingredient?->name,
                    'ingredient_unit' => $item->ingredient?->unit,
                    'ingredient_active' => $item->ingredient?->is_active ?? false,
                    'quantity' => (float) $item->quantity,
                    'sort_order' => $item->sort_order,
                    'notes' => $item->notes,
                ])
                ->values()
                ->all(),
            updated_at: $product->updated_at?->toDateTimeString(),
        );
    }
}
