<?php

namespace Database\Seeders;

use App\Models\CashflowRule;
use App\Models\DailyBriefing;
use App\Models\Ingredient;
use App\Models\IngredientSupplierTerm;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\PurchaseRecommendation;
use App\Models\SeasonalProfile;
use App\Models\Supplier;
use App\Models\SupplierContact;
use App\Models\SupplierScore;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProcurementIntelligenceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@hajnalhej.hu')->first()
            ?? User::query()->first();

        $supplier = Supplier::query()->updateOrCreate(
            ['name' => 'Malom Kft.'],
            [
                'email' => 'rendeles@malomkft.hu',
                'phone' => '+36 1 555 0101',
                'tax_number' => '12345678-2-41',
                'lead_time_days' => 2,
                'minimum_order_value' => 25000,
                'active' => true,
                'currency' => 'HUF',
                'notes' => 'Liszt és gabona alapanyagok',
                'meta' => ['ordering_channel' => 'email'],
            ],
        );

        $ingredient = Ingredient::query()->where('slug', 'buzaliszt')->first()
            ?? Ingredient::query()->where('is_active', true)->orderBy('id')->first();

        $product = Product::query()->where('slug', 'klasszikus-kovaszos-kenyer')->first()
            ?? Product::query()->orderBy('id')->first();

        if (! $ingredient || ! $product) {
            return;
        }

        SupplierContact::query()->updateOrCreate(
            ['supplier_id' => $supplier->id, 'email' => 'beszerzes@malomkft.hu'],
            [
                'name' => 'Kovács Anna',
                'role' => 'Key account',
                'phone' => '+36 30 555 0101',
                'is_primary' => true,
                'active' => true,
                'notes' => 'Heti rendelési egyeztetés.',
            ],
        );

        IngredientSupplierTerm::query()->updateOrCreate(
            ['ingredient_id' => $ingredient->id, 'supplier_id' => $supplier->id],
            [
                'supplier_sku' => 'MALOM-BL80-25KG',
                'lead_time_days' => 2,
                'minimum_order_quantity' => 50,
                'minimum_order_value' => 25000,
                'pack_size' => 25,
                'preferred' => true,
                'unit_cost_override' => 280,
                'last_unit_cost' => 295,
                'average_unit_cost' => 287,
                'payment_term_days' => 8,
                'currency' => 'HUF',
                'valid_from' => Carbon::today()->subMonth()->toDateString(),
                'valid_until' => Carbon::today()->addMonths(3)->toDateString(),
                'quality_threshold_percent' => 98.5,
                'meta' => ['incoterm' => 'delivered'],
            ],
        );

        SeasonalProfile::query()->updateOrCreate(
            ['name' => 'Adventi kenyérkereslet', 'ingredient_id' => $ingredient->id],
            [
                'product_id' => $product->id,
                'profile_type' => 'holiday',
                'starts_on' => Carbon::create(2026, 12, 1)->toDateString(),
                'ends_on' => Carbon::create(2026, 12, 24)->toDateString(),
                'demand_multiplier' => 1.35,
                'confidence_percent' => 82.5,
                'active' => true,
                'notes' => 'Decemberi előrendelések alapján.',
            ],
        );

        CashflowRule::query()->updateOrCreate(
            ['name' => 'Heti beszerzési plafon'],
            [
                'rule_type' => 'weekly_budget_cap',
                'threshold_amount' => 350000,
                'warning_percent' => 85,
                'lookahead_days' => 7,
                'action' => 'warn_owner',
                'active' => true,
                'priority' => 10,
                'conditions' => ['include_open_recommendations' => true],
                'notes' => 'Owner briefingben jelenjen meg.',
            ],
        );

        PricingRule::query()->updateOrCreate(
            ['name' => 'Klasszikus kenyér margin őr', 'product_id' => $product->id],
            [
                'rule_type' => 'target_margin',
                'target_margin_percent' => 62,
                'minimum_margin_percent' => 55,
                'cost_change_threshold_percent' => 7.5,
                'suggested_price' => 2490,
                'active' => true,
                'valid_from' => Carbon::today()->toDateString(),
                'valid_until' => Carbon::today()->addMonths(6)->toDateString(),
                'conditions' => ['review_on_supplier_price_alert' => true],
            ],
        );

        SupplierScore::query()->updateOrCreate(
            [
                'supplier_id' => $supplier->id,
                'period_start' => Carbon::today()->startOfMonth()->toDateString(),
                'period_end' => Carbon::today()->endOfMonth()->toDateString(),
            ],
            [
                'overall_score' => 91.25,
                'price_score' => 88,
                'reliability_score' => 94,
                'quality_score' => 96,
                'lead_time_score' => 87,
                'orders_count' => 8,
                'late_deliveries_count' => 1,
                'rejected_quantity' => 0,
                'score_breakdown' => ['note' => 'Stable strategic flour supplier.'],
            ],
        );

        $recommendation = PurchaseRecommendation::query()->updateOrCreate(
            ['recommendation_number' => 'REC-PI-2026-001'],
            [
                'supplier_id' => $supplier->id,
                'status' => 'draft',
                'recommendation_date' => Carbon::today()->toDateString(),
                'needed_by_date' => Carbon::today()->addDays(3)->toDateString(),
                'estimated_total' => 70000,
                'cashflow_score' => 78,
                'margin_score' => 84,
                'rationale' => 'Low-stock coverage with preferred supplier pack rounding.',
                'created_by' => $admin?->id,
            ],
        );

        $recommendation->items()->updateOrCreate(
            ['ingredient_id' => $ingredient->id],
            [
                'supplier_id' => $supplier->id,
                'current_stock' => $ingredient->current_stock,
                'forecast_demand' => 120,
                'safety_stock' => 30,
                'recommended_quantity' => 250,
                'approved_quantity' => null,
                'unit' => $ingredient->unit,
                'estimated_unit_cost' => 280,
                'estimated_line_total' => 70000,
                'margin_impact' => 12500,
                'reason_code' => 'reorder_point',
                'calculation_snapshot' => ['pack_size' => 25, 'minimum_order_quantity' => 50],
            ],
        );

        DailyBriefing::query()->updateOrCreate(
            ['briefing_date' => Carbon::today()->toDateString()],
            [
                'status' => 'generated',
                'cash_needed_today' => 70000,
                'projected_procurement_total' => 145000,
                'open_alerts_count' => 2,
                'critical_alerts_count' => 0,
                'summary' => ['headline' => 'Beszerzés stabil, liszt utánrendelés javasolt.'],
                'recommended_actions' => [
                    ['type' => 'approve_recommendation', 'reference' => 'REC-PI-2026-001'],
                ],
                'generated_at' => now(),
                'generated_by' => $admin?->id,
            ],
        );
    }
}
