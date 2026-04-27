<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_supplier_terms', function (Blueprint $table): void {
            $table->string('supplier_sku', 120)->nullable()->after('supplier_id');
            $table->decimal('minimum_order_value', 12, 2)->nullable()->after('minimum_order_quantity');
            $table->decimal('last_unit_cost', 12, 2)->nullable()->after('unit_cost_override');
            $table->decimal('average_unit_cost', 12, 2)->nullable()->after('last_unit_cost');
            $table->unsignedInteger('payment_term_days')->nullable()->after('average_unit_cost');
            $table->string('currency', 3)->default('HUF')->after('payment_term_days');
            $table->date('valid_from')->nullable()->after('currency');
            $table->date('valid_until')->nullable()->after('valid_from');
            $table->decimal('quality_threshold_percent', 8, 4)->nullable()->after('valid_until');
            $table->json('meta')->nullable()->after('quality_threshold_percent');

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
