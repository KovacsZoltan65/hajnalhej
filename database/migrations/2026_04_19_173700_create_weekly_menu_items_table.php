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
        Schema::create('weekly_menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('weekly_menu_id')->constrained('weekly_menus')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('override_name')->nullable();
            $table->string('override_short_description')->nullable();
            $table->decimal('override_price', 10, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('badge_text')->nullable();
            $table->string('stock_note')->nullable();
            $table->timestamps();

            $table->unique(['weekly_menu_id', 'product_id']);
            $table->index(['weekly_menu_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_menu_items');
    }
};
