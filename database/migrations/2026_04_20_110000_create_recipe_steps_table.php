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
        Schema::create('recipe_steps', function (Blueprint $table): void {
            $table->id()->comment('Rekord azonosító');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->comment('Kapcsolódó termék azonosító');
            $table->string('title')->comment('Lépés megnevezése');
            $table->string('step_type')->index()->comment('Lépés típusa: preparation|mixing|resting|proofing|baking|cooling|finishing');
            $table->text('description')->nullable()->comment('Lépés részletes leírása');
            $table->unsignedInteger('duration_minutes')->nullable()->comment('Aktív munkaidő percben');
            $table->unsignedInteger('wait_minutes')->nullable()->comment('Várakozási idő percben');
            $table->decimal('temperature_celsius', 5, 1)->nullable()->comment('Hőmérséklet Celsius fokban');
            $table->unsignedInteger('sort_order')->default(0)->index()->comment('Lépés sorrendje a recepten belül');
            $table->boolean('is_active')->default(true)->index()->comment('Aktív lépés jelző');
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_steps');
    }
};

