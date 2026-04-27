<?php

use Illuminate\Support\Facades\Schema;

it('has procurement intelligence tables', function (): void {
    $tables = [
        'supplier_contacts',
        'ingredient_supplier_terms',
        'purchase_receipts',
        'purchase_receipt_items',
        'procurement_alerts',
        'forecast_runs',
        'forecast_snapshots',
        'seasonal_profiles',
        'price_alerts',
        'supplier_scores',
        'purchase_recommendations',
        'purchase_recommendation_items',
        'cashflow_rules',
        'risk_events',
        'branches',
        'branch_inventory',
        'branch_transfers',
        'pricing_rules',
        'supplier_negotiations',
        'daily_briefings',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue($table.' table is missing');
    }
});

it('has important procurement intelligence columns', function (): void {
    expect(Schema::hasColumns('suppliers', [
        'lead_time_days',
        'minimum_order_value',
        'active',
        'currency',
        'meta',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('ingredient_supplier_terms', [
            'supplier_sku',
            'minimum_order_value',
            'last_unit_cost',
            'average_unit_cost',
            'payment_term_days',
            'valid_from',
            'valid_until',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('purchases', [
            'expected_delivery_date',
            'received_date',
            'receipt_status',
            'received_total',
            'ordered_at',
            'cancelled_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('purchase_recommendation_items', [
            'recommended_quantity',
            'approved_quantity',
            'estimated_unit_cost',
            'calculation_snapshot',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('daily_briefings', [
            'briefing_date',
            'summary',
            'recommended_actions',
            'generated_at',
        ]))->toBeTrue();
});
