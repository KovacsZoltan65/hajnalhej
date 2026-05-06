<?php

namespace Database\Seeders\test;

use App\Models\Purchase;
use Illuminate\Database\Seeder;

class RequiredIngredientPurchaseSeeder extends Seeder
{
    public function run(): void
    {
        Purchase::create([
            'supplier_name' => 'Auto Generated Supplier',
            'status' => 'received',
            'notes' => 'Create purchases based on seeded demand manually extend here.',
        ]);
    }
}
