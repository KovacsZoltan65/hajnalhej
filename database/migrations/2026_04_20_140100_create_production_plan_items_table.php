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
        Schema::create('production_plan_items', function (Blueprint $table): void {
            $table->id()->comment('Rekord azonosito');
            $table->foreignId('production_plan_id')->constrained('production_plans')->cascadeOnDelete()->comment('Kapcsolodo gyartasi terv azonosito');
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete()->comment('Tervezett termek azonosito');
            $table->string('product_name_snapshot')->comment('Termek nev snapshot a terv idejen');
            $table->string('product_slug_snapshot')->comment('Termek slug snapshot a terv idejen');
            $table->decimal('target_quantity', 10, 3)->comment('Gyartando mennyiseg');
            $table->string('unit_label', 24)->default('db')->comment('Mennyisegi egyseg jeloles (pl. db)');
            $table->unsignedInteger('sort_order')->default(0)->index()->comment('Tetel sorrend a terven belul');
            $table->unsignedInteger('computed_ingredient_count')->default(0)->comment('Szamitott hozzavalo sorok szama');
            $table->unsignedInteger('computed_step_count')->default(0)->comment('Szamitott receptlepesek szama');
            $table->unsignedInteger('computed_active_minutes')->default(0)->comment('Szamitott aktiv ido percben');
            $table->unsignedInteger('computed_wait_minutes')->default(0)->comment('Szamitott varakozasi ido percben');
            $table->timestamps();

            $table->unique(['production_plan_id', 'product_id']);
            $table->index(['production_plan_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_items');
    }
};

