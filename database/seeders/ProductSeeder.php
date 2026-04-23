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
                'name' => 'Klasszikus kovászos kenyér',
                'slug' => 'klasszikus-kovaszos-kenyer',
                'category_slug' => 'kenyérek',
                'price' => 2450,
                'short_description' => 'Ropogos hej, selymes belso, 24 oras keles.',
                'description' => 'Hagyomanyos kovasszal keszul, hosszu hideg elesztessel. Minden nap frissen sutjuk, szeletelve is kerheto.',
                'image_path' => 'products/klasszikus-kovászos-kenyér.jpg',
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 1,
            ],
            [
                'name' => 'Magvas vekni',
                'slug' => 'magvas-vekni',
                'category_slug' => 'kenyerek',
                'price' => 2690,
                'short_description' => 'Napraforgo, lenmag es szezammag egyensulyban.',
                'description' => 'Ropogos hej es hosszan elalló belso. Reggelihez, szendvicshez es sajttal is idealis.',
                'image_path' => 'products/magvas-vekni.jpg',
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 2,
            ],
            [
                'name' => 'Rozmaringos focaccia',
                'slug' => 'rozmaringos-focaccia',
                'category_slug' => 'sos-pekaru',
                'price' => 1990,
                'short_description' => 'Olivaolajos tészta tengeri sóval es friss rozmaringgal.',
                'description' => 'Laza, levegős belső szerkezet, aranybarna héjjal. Előrendelésben nagyobb tepsiben is elérhető.',
                'image_path' => 'products/rozmaringos-focaccia.jpg',
                'stock_status' => Product::STOCK_PREORDER,
                'sort_order' => 3,
            ],
            [
                'name' => 'Kakaós csiga',
                'slug' => 'kakaos-csiga',
                'category_slug' => 'edes-pekaru',
                'price' => 990,
                'short_description' => 'Vajas leveles tészta, intenziv holland kakaókrémmel.',
                'description' => 'Reggeli kedvenc frissen sütve. Kávé mellé tökéletes választás, gyermekeknek is népszerű.',
                'image_path' => 'products/kakaos-csiga.jpg',
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 4,
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
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'is_active' => true,
                    'is_featured' => false,
                    'stock_status' => $item['stock_status'],
                    'image_path' => $item['image_path'],
                    'sort_order' => $item['sort_order'],
                ],
            );
        }
    }
}
