<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Kenyerek', 'slug' => 'kenyerek', 'sort_order' => 1],
            ['name' => 'Edes pekaru', 'slug' => 'edes-pekaru', 'sort_order' => 2],
            ['name' => 'Sos pekaru', 'slug' => 'sos-pekaru', 'sort_order' => 3],
            ['name' => 'Pizza', 'slug' => 'pizza', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ],
            );
        }
    }
}
