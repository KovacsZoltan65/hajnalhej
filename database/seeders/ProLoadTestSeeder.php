<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProLoadTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProLoadTestCustomerSeeder::class,
            ProOrderLoadTestSeeder::class,
            ProRequiredIngredientPurchaseSeeder::class,
            ProWasteLoadTestSeeder::class,
            ProOperationalNoiseSeeder::class,
        ]);
    }
}
