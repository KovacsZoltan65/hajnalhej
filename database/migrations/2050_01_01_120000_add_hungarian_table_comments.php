<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $comments = [
        'users' => 'Felhasználói fiókok és adminisztrátorok',
        'cache' => 'Alkalmazás gyorsítótár rekordok',
        'jobs' => 'Háttérfolyamat queue feladatok',
        'categories' => 'Termék kategóriák',
        'products' => 'Értékesíthető termékek törzsadatai',
        'weekly_menus' => 'Heti kínálatok és rendelési időszakok',
        'weekly_menu_items' => 'Heti kínálat termékei',
        'ingredients' => 'Alapanyag törzsadatok és készletinformációk',
        'product_ingredients' => 'Termékek recept alapanyag kapcsolatai',
        'recipe_steps' => 'Receptek gyártási lépései',
        'production_plans' => 'Gyártási tervek',
        'production_plan_items' => 'Gyártási tervhez tartozó termékek',
        'production_plan_steps' => 'Gyártási terv idővonal lépései',
        'permissions' => 'Jogosultság definíciók',
        'roles' => 'Felhasználói szerepkörök',
        'model_has_permissions' => 'Modellek közvetlen jogosultság kapcsolatai',
        'model_has_roles' => 'Modellek szerepkör kapcsolatai',
        'role_has_permissions' => 'Szerepkör és jogosultság kapcsolatok',
        'orders' => 'Vásárlói rendelések',
        'order_items' => 'Rendeléshez tartozó termék tételek',
        'activity_log' => 'Rendszer aktivitási és audit napló',
        'conversion_events' => 'Konverziós és marketing események',
        'suppliers' => 'Beszállítók törzsadatai',
        'purchases' => 'Beszerzési rendelések',
        'purchase_items' => 'Beszerzési rendelés tételek',
        'inventory_movements' => 'Készletmozgás napló',
        'stock_counts' => 'Készletellenőrzési jegyzőkönyvek',
        'stock_count_items' => 'Készletellenőrzési tételek',
        'ingredient_supplier_terms' => 'Beszállítói alapanyag feltételek',
        'supplier_contacts' => 'Beszállítói kapcsolattartók',
        'purchase_receipts' => 'Beszerzési átvételek',
        'purchase_receipt_items' => 'Beszerzési átvételi tételek',
        'procurement_alerts' => 'Beszerzési figyelmeztetések és riasztások',
        'forecast_runs' => 'Előrejelzés futtatási naplók',
        'forecast_snapshots' => 'Előrejelzési pillanatképek',
        'seasonal_profiles' => 'Szezonális keresleti profilok',
        'price_alerts' => 'Árfigyelési riasztások',
        'supplier_scores' => 'Beszállítói teljesítmény értékelések',
        'purchase_recommendations' => 'Automatikus beszerzési javaslatok',
        'purchase_recommendation_items' => 'Beszerzési javaslat tételek',
        'cashflow_rules' => 'Cashflow és fizetési szabályok',
        'risk_events' => 'Üzleti és ellátási kockázati események',
        'branches' => 'Telephelyek és üzletek',
        'branch_inventory' => 'Telephelyenkénti készlet adatok',
        'branch_transfers' => 'Telephelyek közötti készletmozgások',
        'pricing_rules' => 'Árképzési szabályok',
        'supplier_negotiations' => 'Beszállítói tárgyalási naplók',
        'daily_briefings' => 'Napi vezetői összefoglalók',
        'user_temporary_permissions' => 'Felhasználói ideiglenes jogosultságok',
        'user_discounts' => 'Felhasználóhoz rendelt kedvezmények',
        'failed_jobs' => 'Sikertelen queue feladatok',
        'password_reset_tokens' => 'Jelszó visszaállítási tokenek',
        'sessions' => 'Aktív felhasználói munkamenetek',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->comments as $table => $comment) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) use ($comment) {
                $table->comment($comment);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (array_keys($this->comments) as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                $table->comment('');
            });
        }
    }
};
