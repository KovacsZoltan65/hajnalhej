<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasonal_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->nullable()->comment('Kapcsolódó alapanyag')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->comment('Kapcsolódó termék')->constrained('products')->cascadeOnDelete();
            $table->string('name', 160)->comment('Szezonális profil neve');
            $table->string('profile_type', 48)->index()->comment('Szezonális profil típusa');
            $table->date('starts_on')->index()->comment('Szezonális profil dátuma');
            $table->date('ends_on')->index()->comment('Szezonális profil dátuma');
            $table->decimal('demand_multiplier', 8, 4)->default(1)->comment('Szezonális profil Demand multiplier');
            $table->decimal('confidence_percent', 8, 4)->nullable()->comment('Confidence percent');
            $table->boolean('active')->default(true)->index()->comment('Aktív-e');
            $table->text('notes')->nullable()->comment('Szezonális profil megjegyzése');
            $table->timestamps();

            $table->index(['active', 'starts_on', 'ends_on']);
            $table->index(['ingredient_id', 'active']);
            $table->index(['product_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_profiles');
    }
};
