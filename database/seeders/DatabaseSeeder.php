<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@hajnalhej.hu'],
            [
                'name' => 'Hajnalhej Admin',
                'password' => 'bakery1234',
            ],
        );

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            IngredientSeeder::class,
            ProductIngredientSeeder::class,
            RecipeStepSeeder::class,
            WeeklyMenuSeeder::class,

            // Anyakovász
            MotherStarterRecipeSeeder::class,
            // Aktív kovász
            SourdoughStarterRecipeSeeder::class,
            // Egyszerű kovászos fehérkenyér
            SourdoughBeginnerRecipeSeeder::class,
            // 
            SourdoughArtisanRecipeSeeder::class,
            // Kovászos bagett
            SourdoughSpecialtyRecipesSeeder::class,
            // Ciabatta
            SourdoughCiabattaSeeder::class,
            // Nápoji pizza
            SourdoughNapoliPizzaSeeder::class,
            // Kakaós csiga
            SourdoughCocoaRollSeeder::class,
            // Briós
            EnrichedAndTrayBakeRecipesSeeder::class,

            // Premium Bakery Pack
            // Bagel, Croisant, Fahéjas cdiga
            PremiumBakerySeedPackSeeder::class,

            ProductionPlanSeeder::class,
        ]);
    }
}
