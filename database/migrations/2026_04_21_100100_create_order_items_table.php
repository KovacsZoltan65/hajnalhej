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
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->comment('Kapcsolódó rendelés')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->comment('Kapcsolódó termék')->constrained()->nullOnDelete();
            $table->string('product_name_snapshot', 160)->comment('Rendeléskori terméknév pillanatképe');
            $table->decimal('unit_price', 12, 2)->comment('Egységár');
            $table->unsignedInteger('quantity')->comment('Mennyiség');
            $table->decimal('line_total', 12, 2)->comment('Tétel sorösszege');
            $table->json('recipe_snapshot')->nullable()->comment('Rendelési tétel recept pillanatképe JSON formátumban');
            $table->json('metadata')->nullable()->comment('Rendelési tétel kiegészítő JSON adatai');
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
