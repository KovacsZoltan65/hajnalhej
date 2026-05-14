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
            $table->foreignId('product_id')->nullable()->comment('Kapcsolódó termék')->constrained('products')->cascadeOnDelete();
            $table->string('name', 160)->comment('Árképzési szabály neve');
            $table->string('rule_type', 48)->index()->comment('Árképzési szabály típusa');
            $table->decimal('target_margin_percent', 8, 4)->nullable()->comment('Cél árrés százalékban');
            $table->decimal('minimum_margin_percent', 8, 4)->nullable()->comment('Minimális árrés százalékban');
            $table->decimal('cost_change_threshold_percent', 8, 4)->nullable()->comment('Költségváltozási küszöb százalékban');
            $table->decimal('suggested_price', 12, 2)->nullable()->comment('Javasolt eladási ár');
            $table->boolean('active')->default(true)->index()->comment('Aktív-e');
            $table->date('valid_from')->nullable()->index()->comment('Árképzési szabály érvényességének kezdete');
            $table->date('valid_until')->nullable()->index()->comment('Árképzési szabály érvényességének vége');
            $table->json('conditions')->nullable()->comment('Árképzési szabály feltételei JSON formátumban');
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
