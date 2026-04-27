<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PermissionRegistry;
use Database\Seeders\test\LoadTestCustomerSeeder;
use Database\Seeders\test\LoadTestSeeder;
use Database\Seeders\test\OrderLoadTestSeeder;
//use Database\Seeders\test\ProLoadTestCustomerSeeder;
//use Database\Seeders\test\ProLoadTestSeeder;
//use Database\Seeders\test\ProOrderLoadTestSeeder;
//use Database\Seeders\test\ProRequiredIngredientPurchaseSeeder;
use Database\Seeders\test\RequiredIngredientPurchaseSeeder;
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
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'admin@hajnalhej.hu'],
            [
                'name' => 'Hajnalhej Admin',
                'password' => 'bakery1234',
            ],
        );

        $admin = User::query()->where('email', 'admin@hajnalhej.hu')->first();
        $admin?->syncRoles([PermissionRegistry::ROLE_ADMIN]);

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
            InventoryProcurementSeeder::class,
            ProcurementIntelligenceSeeder::class,

            // TESZT SEEDEREK
            //LoadTestCustomerSeeder::class,
            //LoadTestSeeder::class,
            //OrderLoadTestSeeder::class,
            //RequiredIngredientPurchaseSeeder::class,

            // PRO TEST SEEDERS
            ProLoadTestSeeder::class,

        ]);
    }
}
