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
        Schema::table('recipe_steps', function (Blueprint $table): void {
            $table->text('work_instruction')->nullable()->after('description')->comment('Mit kell vegrehajtani pontosan');
            $table->text('completion_criteria')->nullable()->after('work_instruction')->comment('Mibol latszik, hogy a lepes kesz');
            $table->text('attention_points')->nullable()->after('completion_criteria')->comment('Kritikus figyelmeztetesek, hibalehetosegek');
            $table->text('required_tools')->nullable()->after('attention_points')->comment('Szukseges eszkozok, gepek, segedanyagok');
            $table->text('expected_result')->nullable()->after('required_tools')->comment('Elvart allapot vagy kimenet a lepes vegen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_steps', function (Blueprint $table): void {
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

