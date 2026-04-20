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
        Schema::create('products', function (Blueprint $table): void {
            $table->id()->comment('Rekord azonosító');
            $table->foreignId('category_id')->constrained('categories')->comment('Kapcsolódó kategória azonosító');
            $table->string('name')->comment('Megnevezés');
            $table->string('slug')->unique()->comment('Egyedi URL azonosító, SEO célra');
            $table->string('short_description')->nullable()->comment('Rövid leírás');
            $table->text('description')->nullable()->comment('Részletes termékleírás');
            $table->decimal('price', 10, 2)->comment('Ár');
            $table->boolean('is_active')->default(true)->index()->comment('Publikus láthatóság státusza');
            $table->boolean('is_featured')->default(false)->index()->comment('Kiemelt termék jelző');
            $table->string('stock_status')->default('in_stock')->index()->comment('Készletállapot: in_stock|preorder|out_of_stock');
            $table->string('image_path')->nullable()->comment('Kép útvonala');
            $table->unsignedInteger('sort_order')->default(0)->index()->comment('Admin listázási sorrend');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete időpontja');

            $table->index(['category_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
