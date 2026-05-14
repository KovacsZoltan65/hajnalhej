<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('production_plan_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('production_plan_id')->constrained('production_plans')->cascadeOnDelete()->comment('Kapcsolódó gyártási terv');
            $table->foreignId('production_plan_item_id')->nullable()->constrained('production_plan_items')->nullOnDelete()->comment('Kapcsolódó gyártási tétel');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->comment('Érintett termék');
            $table->foreignId('depends_on_product_id')->nullable()->constrained('products')->nullOnDelete()->comment('Függőséget adó termék');
            $table->string('title')->comment('Gyártási lépés megnevezése');
            $table->string('step_type')->index()->comment('Gyártási lépés típusa');
            $table->text('description')->nullable()->comment('Gyártási lépés részletes leírása');
            $table->dateTime('starts_at')->index()->comment('Gyártási lépés kezdete');
            $table->dateTime('ends_at')->index()->comment('Gyártási lépés vége');
            $table->unsignedInteger('duration_minutes')->default(0)->comment('Aktiv ido percben');
            $table->unsignedInteger('wait_minutes')->default(0)->comment('Varakozasi ido percben');
            $table->unsignedInteger('sort_order')->default(0)->index()->comment('Globális ütemezési sorrend');
            $table->string('timeline_group', 120)->nullable()->comment('Ütemezési csoport azonosítója');
            $table->boolean('is_dependency')->default(false)->index()->comment('Függőségi lépés-e');
            $table->json('meta')->nullable()->comment('Gyártási lépés kiegészítő JSON adatai');
            $table->timestamps();

            $table->index(['production_plan_id', 'starts_at']);
            $table->index(['production_plan_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_steps');
    }
};
