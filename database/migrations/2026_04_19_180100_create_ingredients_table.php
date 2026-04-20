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
        Schema::create('ingredients', function (Blueprint $table): void {
            $table->id()->comment('Rekord azonosító');
            $table->string('name')->unique()->comment('Megnevezés');
            $table->string('slug')->unique()->comment('Egyedi URL azonosító, SEO célra');
            $table->string('sku')->nullable()->unique()->comment('Belső cikkszám');
            $table->string('unit', 16)->index()->comment('Mértékegység (pl. kg, g, l, db)');
            $table->decimal('current_stock', 12, 3)->default(0)->comment('Aktuális készlet');
            $table->decimal('minimum_stock', 12, 3)->default(0)->comment('Minimum készletszint');
            $table->boolean('is_active')->default(true)->index()->comment('Felhasználhatóság státusza');
            $table->text('notes')->nullable()->comment('Belső megjegyzés');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete időpontja');

            $table->index(['is_active', 'unit']);
            $table->index(['current_stock', 'minimum_stock']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
