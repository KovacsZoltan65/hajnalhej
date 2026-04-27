<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('rule_type', 48)->index();
            $table->decimal('target_margin_percent', 8, 4)->nullable();
            $table->decimal('minimum_margin_percent', 8, 4)->nullable();
            $table->decimal('cost_change_threshold_percent', 8, 4)->nullable();
            $table->decimal('suggested_price', 12, 2)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->date('valid_from')->nullable()->index();
            $table->date('valid_until')->nullable()->index();
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'active']);
            $table->index(['active', 'valid_from', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
