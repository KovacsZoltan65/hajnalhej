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
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('profile_type', 48)->index();
            $table->date('starts_on')->index();
            $table->date('ends_on')->index();
            $table->decimal('demand_multiplier', 8, 4)->default(1);
            $table->decimal('confidence_percent', 8, 4)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->text('notes')->nullable();
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
