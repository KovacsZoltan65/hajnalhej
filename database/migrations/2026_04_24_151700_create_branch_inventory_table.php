<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_inventory', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->comment('Kapcsolódó telephely')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->comment('Kapcsolódó alapanyag')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('current_stock', 12, 3)->default(0)->comment('Aktuális készlet');
            $table->decimal('reserved_stock', 12, 3)->default(0)->comment('Reserved stock');
            $table->decimal('minimum_stock', 12, 3)->default(0)->comment('Minimum stock');
            $table->decimal('reorder_point', 12, 3)->default(0)->comment('Telephelyi készlet Reorder point');
            $table->decimal('target_stock', 12, 3)->nullable()->comment('Célkészlet');
            $table->timestamp('last_counted_at')->nullable()->index()->comment('Telephelyi készlet időpontja');
            $table->timestamps();

            $table->unique(['branch_id', 'ingredient_id']);
            $table->index(['ingredient_id', 'current_stock']);
            $table->index(['branch_id', 'current_stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_inventory');
    }
};
