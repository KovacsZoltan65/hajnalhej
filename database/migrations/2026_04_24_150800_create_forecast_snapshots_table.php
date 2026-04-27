<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forecast_run_id')->constrained('forecast_runs')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->date('forecast_date')->index();
            $table->decimal('forecast_quantity', 12, 3);
            $table->decimal('actual_quantity', 12, 3)->nullable();
            $table->decimal('variance_quantity', 12, 3)->nullable();
            $table->decimal('confidence_percent', 8, 4)->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->json('drivers')->nullable();
            $table->timestamps();

            $table->unique(['forecast_run_id', 'ingredient_id', 'forecast_date'], 'forecast_snapshots_unique');
            $table->index(['ingredient_id', 'forecast_date']);
            $table->index(['product_id', 'forecast_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_snapshots');
    }
};
