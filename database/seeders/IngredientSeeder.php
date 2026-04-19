<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            [
                'name' => 'Buzaliszt',
                'slug' => 'buzaliszt',
                'sku' => 'ING-BUZALISZT',
                'unit' => 'kg',
                'current_stock' => 45,
                'minimum_stock' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Rozs liszt',
                'slug' => 'rozs-liszt',
                'sku' => 'ING-ROZSLISZT',
                'unit' => 'kg',
                'current_stock' => 18,
                'minimum_stock' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'So',
                'slug' => 'so',
                'sku' => 'ING-SO',
                'unit' => 'kg',
                'current_stock' => 4,
                'minimum_stock' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Cukor',
                'slug' => 'cukor',
                'sku' => 'ING-CUKOR',
                'unit' => 'kg',
                'current_stock' => 6,
                'minimum_stock' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Eleszto',
                'slug' => 'eleszto',
                'sku' => 'ING-ELESZTO',
                'unit' => 'g',
                'current_stock' => 450,
                'minimum_stock' => 500,
                'is_active' => true,
            ],
            [
                'name' => 'Vaj',
                'slug' => 'vaj',
                'sku' => 'ING-VAJ',
                'unit' => 'kg',
                'current_stock' => 3.5,
                'minimum_stock' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Tej',
                'slug' => 'tej',
                'sku' => 'ING-TEJ',
                'unit' => 'l',
                'current_stock' => 8,
                'minimum_stock' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Tojas',
                'slug' => 'tojas',
                'sku' => 'ING-TOJAS',
                'unit' => 'db',
                'current_stock' => 90,
                'minimum_stock' => 40,
                'is_active' => true,
            ],
        ];

        foreach ($ingredients as $item) {
            Ingredient::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'unit' => $item['unit'],
                    'current_stock' => $item['current_stock'],
                    'minimum_stock' => $item['minimum_stock'],
                    'is_active' => $item['is_active'],
                    'notes' => null,
                ],
            );
        }
    }
}
