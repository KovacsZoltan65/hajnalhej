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
        Schema::create('product_ingredients', function (Blueprint $table): void {
            $table->id()->comment('Rekord azonosító');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->comment('Kapcsolódó termék azonosító');
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete()->comment('Kapcsolódó alapanyag azonosító');
            $table->decimal('quantity', 12, 3)->comment('Felhasznált mennyiség');
            $table->unsignedInteger('sort_order')->default(0)->index()->comment('Recepten belüli sorrend');
            $table->text('notes')->nullable()->comment('Recept tétel megjegyzése');
            $table->timestamps();

            $table->unique(['product_id', 'ingredient_id']);
            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_ingredients');
    }
};
