<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_count_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stock_count_id')->comment('Kapcsolódó leltár')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->comment('Kapcsolódó alapanyag')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('expected_quantity', 14, 3)->comment('Expected quantity');
            $table->decimal('counted_quantity', 14, 3)->comment('Megszámolt mennyiség');
            $table->decimal('difference', 14, 3)->comment('Leltári tétel Difference');
            $table->timestamps();

            $table->unique(['stock_count_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
    }
};
