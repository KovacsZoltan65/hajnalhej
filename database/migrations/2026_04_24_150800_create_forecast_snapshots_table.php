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
            $table->foreignId('forecast_run_id')->comment('Kapcsolódó előrejelzés futtatás')->constrained('forecast_runs')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->comment('Kapcsolódó alapanyag')->constrained('ingredients')->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->comment('Kapcsolódó termék')->constrained('products')->nullOnDelete();
            $table->date('forecast_date')->index()->comment('Előrejelzési pillanatkép dátuma');
            $table->decimal('forecast_quantity', 12, 3)->comment('Előrejelzett mennyiség');
            $table->decimal('actual_quantity', 12, 3)->nullable()->comment('Actual quantity');
            $table->decimal('variance_quantity', 12, 3)->nullable()->comment('Variance quantity');
            $table->decimal('confidence_percent', 8, 4)->nullable()->comment('Confidence percent');
            $table->decimal('estimated_cost', 12, 2)->nullable()->comment('Estimated cost');
            $table->json('drivers')->nullable()->comment('Előrejelzési pillanatkép számítási tényezői JSON formátumban');
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
