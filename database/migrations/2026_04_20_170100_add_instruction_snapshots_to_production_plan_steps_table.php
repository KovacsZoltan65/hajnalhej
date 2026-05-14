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
        Schema::table('production_plan_steps', function (Blueprint $table): void {
            $table->text('work_instruction')->nullable()->after('description')->comment('Végrehajtandó művelet pillanatképe');
            $table->text('completion_criteria')->nullable()->after('work_instruction')->comment('Lépés elkészülési feltételének pillanatképe');
            $table->text('attention_points')->nullable()->after('completion_criteria')->comment('Kritikus figyelmeztetések pillanatképe');
            $table->text('required_tools')->nullable()->after('attention_points')->comment('Szükséges eszközök pillanatképe');
            $table->text('expected_result')->nullable()->after('required_tools')->comment('Lépés elvárt eredményének pillanatképe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_plan_steps', function (Blueprint $table): void {
            $table->dropColumn([
                'work_instruction',
                'completion_criteria',
                'attention_points',
                'required_tools',
                'expected_result',
            ]);
        });
    }
};
