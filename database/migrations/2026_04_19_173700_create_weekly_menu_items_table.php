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
            $table->id()->comment('Rekord azonosító');
            $table->foreignId('weekly_menu_id')->constrained('weekly_menus')->cascadeOnDelete()->comment('Kapcsolódó heti menü azonosító');
            $table->foreignId('product_id')->constrained('products')->comment('Kapcsolódó termék azonosító');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->comment('Megjelenített kategória azonosító');
            $table->string('override_name')->nullable()->comment('Heti menüben felülírt terméknév');
            $table->string('override_short_description')->nullable()->comment('Heti menüben felülírt rövid leírás');
            $table->decimal('override_price', 10, 2)->nullable()->comment('Heti menüben felülírt ár');
            $table->unsignedInteger('sort_order')->default(0)->index()->comment('Heti menün belüli sorrend');
            $table->boolean('is_active')->default(true)->index()->comment('Aktív megjelenés a heti menüben');
            $table->string('badge_text')->nullable()->comment('Termékkártya badge felirat');
            $table->string('stock_note')->nullable()->comment('Készlettel kapcsolatos rövid megjegyzés');
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
