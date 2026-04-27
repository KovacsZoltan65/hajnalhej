<?php

namespace Database\Seeders\test;

use Illuminate\Database\Seeder;

class LoadTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LoadTestCustomerSeeder::class,
            OrderLoadTestSeeder::class,
            RequiredIngredientPurchaseSeeder::class,
        ]);
    }
}
