<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            $table->unsignedSmallInteger('lead_time_days')->nullable()->after('tax_number');
        });

        Schema::create('ingredient_supplier_terms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->unsignedSmallInteger('lead_time_days')->nullable();
            $table->decimal('minimum_order_quantity', 12, 3)->nullable();
            $table->decimal('pack_size', 12, 3)->nullable();
            $table->boolean('preferred')->default(false)->index();
            $table->decimal('unit_cost_override', 12, 4)->nullable();
            $table->timestamps();

            $table->unique(['ingredient_id', 'supplier_id'], 'ingredient_supplier_terms_unique');
            $table->index(['ingredient_id', 'preferred']);
            $table->index(['supplier_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_supplier_terms');

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropColumn('lead_time_days');
        });
    }
};
