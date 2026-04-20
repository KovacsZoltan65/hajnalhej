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
        Schema::create('categories', function (Blueprint $table): void {
            $table->id()->comment('Rekord azonosító');
            $table->string('name')->unique()->comment('Megnevezés');
            $table->string('slug')->unique()->comment('Egyedi URL azonosító, SEO célra');
            $table->text('description')->nullable()->comment('Leírás');
            $table->boolean('is_active')->default(true)->index()->comment('Publikus láthatóság státusza');
            $table->unsignedInteger('sort_order')->default(0)->index()->comment('Admin listázási sorrend');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete időpontja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
