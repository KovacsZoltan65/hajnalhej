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
        Schema::create('weekly_menus', function (Blueprint $table): void {
            $table->id()->comment('Rekord azonosító');
            $table->string('title')->comment('Heti menü admin címe');
            $table->string('slug')->unique()->comment('Egyedi URL azonosító, SEO célra');
            $table->date('week_start')->index()->comment('A heti menü kezdődátuma');
            $table->date('week_end')->index()->comment('A heti menü záródátuma');
            $table->string('status')->default('draft')->index()->comment('Állapot: draft|published|archived');
            $table->text('public_note')->nullable()->comment('Vásárlóknak megjelenő megjegyzés');
            $table->text('internal_note')->nullable()->comment('Belső üzemeltetési megjegyzés');
            $table->boolean('is_featured')->default(false)->index()->comment('Kiemelt heti menü jelző');
            $table->timestamp('published_at')->nullable()->comment('Publikálás időpontja');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete időpontja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_menus');
    }
};
