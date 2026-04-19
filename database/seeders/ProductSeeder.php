<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Klasszikus kovaszos kenyer',
                'slug' => 'klasszikus-kovaszos-kenyer',
                'category_slug' => 'kenyerek',
                'price' => 2450,
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 1,
            ],
            [
                'name' => 'Magvas vekni',
                'slug' => 'magvas-vekni',
                'category_slug' => 'kenyerek',
                'price' => 2690,
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 2,
            ],
            [
                'name' => 'Rozmaringos focaccia',
                'slug' => 'rozmaringos-focaccia',
                'category_slug' => 'sos-pekaru',
                'price' => 1990,
                'stock_status' => Product::STOCK_PREORDER,
                'sort_order' => 3,
            ],
        ];

        foreach ($products as $item) {
            $category = Category::query()->where('slug', $item['category_slug'])->first();

            if (! $category) {
                continue;
            }

            Product::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'short_description' => null,
                    'description' => null,
                    'price' => $item['price'],
                    'is_active' => true,
                    'is_featured' => false,
                    'stock_status' => $item['stock_status'],
                    'image_path' => null,
                    'sort_order' => $item['sort_order'],
                ],
            );
        }
    }
}
