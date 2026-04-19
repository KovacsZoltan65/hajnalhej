<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use Illuminate\Database\Seeder;

class ProductIngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipes = [
            'klasszikus-kovaszos-kenyer' => [
                ['ingredient' => 'buzaliszt', 'quantity' => 0.800, 'sort_order' => 1],
                ['ingredient' => 'so', 'quantity' => 0.018, 'sort_order' => 2],
                ['ingredient' => 'eleszto', 'quantity' => 2.000, 'sort_order' => 3],
            ],
            'magvas-vekni' => [
                ['ingredient' => 'buzaliszt', 'quantity' => 0.600, 'sort_order' => 1],
                ['ingredient' => 'rozs-liszt', 'quantity' => 0.200, 'sort_order' => 2],
                ['ingredient' => 'so', 'quantity' => 0.019, 'sort_order' => 3],
            ],
            'rozmaringos-focaccia' => [
                ['ingredient' => 'buzaliszt', 'quantity' => 0.500, 'sort_order' => 1],
                ['ingredient' => 'eleszto', 'quantity' => 1.500, 'sort_order' => 2],
            ],
        ];

        foreach ($recipes as $productSlug => $items) {
            $product = Product::query()->where('slug', $productSlug)->first();

            if (! $product) {
                continue;
            }

            foreach ($items as $item) {
                $ingredient = Ingredient::query()->where('slug', $item['ingredient'])->first();

                if (! $ingredient) {
                    continue;
                }

                ProductIngredient::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'ingredient_id' => $ingredient->id,
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'sort_order' => $item['sort_order'],
                        'notes' => null,
                    ],
                );
            }
        }
    }
}
