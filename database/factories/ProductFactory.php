<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Klasszikus kovaszos kenyer',
            'Magvas vekni',
            'Rozsos parasztkenyer',
            'Rozmaringos focaccia',
            'Kakaos csiga',
            'Fahejas tekercs',
            'Vajas croissant',
            'Brioche kalacs',
        ]).' '.fake()->unique()->numerify('###');

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => fake()->optional()->sentence(8),
            'description' => fake()->optional()->paragraphs(2, true),
            'price' => fake()->randomFloat(2, 590, 4990),
            'is_active' => fake()->boolean(85),
            'is_featured' => fake()->boolean(30),
            'stock_status' => fake()->randomElement(Product::stockStatuses()),
            'image_path' => fake()->optional(40)->randomElement([
                'products/klasszikus-kovaszos-kenyer.jpg',
                'products/magvas-vekni.jpg',
                'products/rozmaringos-focaccia.jpg',
                'products/kakaos-csiga.jpg',
            ]),
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
