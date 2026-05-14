<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_recommendation_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_recommendation_id')->comment('Kapcsolódó beszerzési javaslat')->constrained('purchase_recommendations')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->comment('Kapcsolódó alapanyag')->constrained('ingredients')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->comment('Kapcsolódó beszállító')->constrained('suppliers')->nullOnDelete();
            $table->decimal('current_stock', 12, 3)->default(0)->comment('Aktuális készlet');
            $table->decimal('forecast_demand', 12, 3)->default(0)->comment('Előrejelzett kereslet');
            $table->decimal('safety_stock', 12, 3)->default(0)->comment('Biztonsági készlet');
            $table->decimal('recommended_quantity', 12, 3)->comment('Recommended quantity');
            $table->decimal('approved_quantity', 12, 3)->nullable()->comment('Approved quantity');
            $table->string('unit', 16)->comment('Beszerzési javaslat tétel mértékegysége');
            $table->decimal('estimated_unit_cost', 12, 2)->nullable()->comment('Estimated unit cost');
            $table->decimal('estimated_line_total', 12, 2)->nullable()->comment('Estimated line total');
            $table->decimal('margin_impact', 12, 2)->nullable()->comment('Árrésre gyakorolt becsült hatás');
            $table->string('reason_code', 64)->nullable()->index()->comment('Beszerzési javaslat tétel kódja');
            $table->json('calculation_snapshot')->nullable()->comment('Beszerzési javaslat tétel számítási pillanatképe JSON formátumban');
            $table->timestamps();

            $table->unique(['purchase_recommendation_id', 'ingredient_id'], 'purchase_recommendation_items_unique');
            $table->index(['ingredient_id', 'supplier_id'], 'pri_ingredient_supplier_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_recommendation_items');
    }
};
