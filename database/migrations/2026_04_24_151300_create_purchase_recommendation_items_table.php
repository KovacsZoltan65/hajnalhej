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
            $table->foreignId('purchase_recommendation_id')->constrained('purchase_recommendations')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('forecast_demand', 12, 3)->default(0);
            $table->decimal('safety_stock', 12, 3)->default(0);
            $table->decimal('recommended_quantity', 12, 3);
            $table->decimal('approved_quantity', 12, 3)->nullable();
            $table->string('unit', 16);
            $table->decimal('estimated_unit_cost', 12, 2)->nullable();
            $table->decimal('estimated_line_total', 12, 2)->nullable();
            $table->decimal('margin_impact', 12, 2)->nullable();
            $table->string('reason_code', 64)->nullable()->index();
            $table->json('calculation_snapshot')->nullable();
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
