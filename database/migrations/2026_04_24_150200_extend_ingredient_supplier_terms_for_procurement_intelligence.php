<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_supplier_terms', function (Blueprint $table): void {
            $table->string('supplier_sku', 120)->nullable()->after('supplier_id')->comment('Beszállítói alapanyag feltétel cikkszáma');
            $table->decimal('minimum_order_value', 12, 2)->nullable()->after('minimum_order_quantity')->comment('Minimális rendelési érték');
            $table->decimal('last_unit_cost', 12, 2)->nullable()->after('unit_cost_override')->comment('Last unit cost');
            $table->decimal('average_unit_cost', 12, 2)->nullable()->after('last_unit_cost')->comment('Average unit cost');
            $table->unsignedInteger('payment_term_days')->nullable()->after('average_unit_cost')->comment('Payment term days');
            $table->string('currency', 3)->default('HUF')->after('payment_term_days')->comment('Beszállítói alapanyag feltétel pénzneme');
            $table->date('valid_from')->nullable()->after('currency')->comment('Beszállítói feltétel érvényességének kezdete');
            $table->date('valid_until')->nullable()->after('valid_from')->comment('Beszállítói feltétel érvényességének vége');
            $table->decimal('quality_threshold_percent', 8, 4)->nullable()->after('valid_until')->comment('Quality threshold percent');
            $table->json('meta')->nullable()->after('quality_threshold_percent')->comment('Beszállítói alapanyag feltétel kiegészítő JSON adatai');

            $table->index(['supplier_id', 'preferred', 'valid_until'], 'ist_supplier_preferred_valid_index');
            $table->index(['ingredient_id', 'last_unit_cost'], 'ist_ingredient_cost_index');
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_supplier_terms', function (Blueprint $table): void {
            $table->dropIndex('ist_supplier_preferred_valid_index');
            $table->dropIndex('ist_ingredient_cost_index');
            $table->dropColumn([
                'supplier_sku',
                'minimum_order_value',
                'last_unit_cost',
                'average_unit_cost',
                'payment_term_days',
                'currency',
                'valid_from',
                'valid_until',
                'quality_threshold_percent',
                'meta',
            ]);
        });
    }
};
