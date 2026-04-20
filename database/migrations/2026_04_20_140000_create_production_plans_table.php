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
        Schema::create('production_plans', function (Blueprint $table): void {
            $table->id()->comment('Rekord azonosito');
            $table->string('plan_number')->unique()->comment('Tervezes egyedi azonositoja');
            $table->dateTime('target_at')->index()->comment('Teljesites celido (mikorra legyen kesz)');
            $table->string('status')->default('draft')->index()->comment('Allapot: draft|calculated|ready|archived');
            $table->unsignedInteger('total_active_minutes')->default(0)->comment('Osszes aktiv munkaido percben');
            $table->unsignedInteger('total_wait_minutes')->default(0)->comment('Osszes varakozasi ido percben');
            $table->unsignedInteger('total_recipe_minutes')->default(0)->comment('Osszes receptido percben');
            $table->dateTime('planned_start_at')->nullable()->index()->comment('Javasolt kezdes a celidohoz visszaszamolva');
            $table->boolean('is_locked')->default(false)->index()->comment('Lezaras jelzo: szerkesztheto-e a terv');
            $table->text('notes')->nullable()->comment('Belso megjegyzes');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('Letrehozo felhasznalo azonosito');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plans');
    }
};

