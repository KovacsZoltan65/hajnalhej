<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_id')->comment('Kapcsolódó beszerzés')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->comment('Kapcsolódó alapanyag')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('quantity', 14, 3)->comment('Mennyiség');
            $table->string('unit', 16)->comment('Beszerzési tétel mértékegysége');
            $table->decimal('unit_cost', 14, 4)->comment('Egységköltség');
            $table->decimal('line_total', 14, 2)->comment('Tétel sorösszege');
            $table->timestamps();

            $table->index(['purchase_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
