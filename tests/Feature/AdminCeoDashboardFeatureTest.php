<?php

use App\Models\ConversionEvent;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Mail\CeoExecutiveReportMail;
use App\Support\ConversionEventRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('admin can access ceo dashboard', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/ceo-dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CeoDashboard/Index')
            ->where('filters.days', 30)
            ->has('dashboard.summary')
            ->has('dashboard.kpi_insights')
            ->has('dashboard.comparisons.wow')
            ->has('dashboard.comparisons.mom')
            ->has('dashboard.conversion')
            ->has('dashboard.top_products')
            ->has('dashboard.security_alerts')
            ->has('dashboard.audit_highlights')
            ->has('dashboard.order_profit_trend.points'));
});

it('customer cannot access ceo dashboard', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/ceo-dashboard')
        ->assertForbidden();
});

it('ceo dashboard shows computed business and conversion metrics', function (): void {
    $admin = User::factory()->admin()->create();
    $repeatCustomer = User::factory()->customer()->create(['email' => 'visszatero@hajnalhej.hu']);
    $singleCustomer = User::factory()->customer()->create(['email' => 'egyszeri@hajnalhej.hu']);

    $ingredient = Ingredient::factory()->create([
        'name' => 'Liszt',
        'estimated_unit_cost' => 500,
    ]);

    $product = Product::factory()->create([
        'name' => 'Kovászos cipó',
        'price' => 2000,
        'is_active' => true,
    ]);

    $product->productIngredients()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 1,
        'sort_order' => 1,
    ]);

    $firstOrder = Order::factory()->create([
        'user_id' => $repeatCustomer->id,
        'customer_email' => $repeatCustomer->email,
        'status' => Order::STATUS_COMPLETED,
        'placed_at' => now()->subDays(2),
        'subtotal' => 4000,
        'total' => 4000,
    ]);
    $firstOrder->items()->create([
        'product_id' => $product->id,
        'product_name_snapshot' => $product->name,
        'unit_price' => 2000,
        'quantity' => 2,
        'line_total' => 4000,
    ]);

    $secondOrder = Order::factory()->create([
        'user_id' => $repeatCustomer->id,
        'customer_email' => $repeatCustomer->email,
        'status' => Order::STATUS_CONFIRMED,
        'placed_at' => now()->subDay(),
        'subtotal' => 2000,
        'total' => 2000,
    ]);
    $secondOrder->items()->create([
        'product_id' => $product->id,
        'product_name_snapshot' => $product->name,
        'unit_price' => 2000,
        'quantity' => 1,
        'line_total' => 2000,
    ]);

    $thirdOrder = Order::factory()->create([
        'user_id' => $singleCustomer->id,
        'customer_email' => $singleCustomer->email,
        'status' => Order::STATUS_READY_FOR_PICKUP,
        'placed_at' => now()->subHours(8),
        'subtotal' => 2000,
        'total' => 2000,
    ]);
    $thirdOrder->items()->create([
        'product_id' => $product->id,
        'product_name_snapshot' => $product->name,
        'unit_price' => 2000,
        'quantity' => 1,
        'line_total' => 2000,
    ]);

    foreach (range(1, 4) as $idx) {
        ConversionEvent::query()->create([
            'event_key' => ConversionEventRegistry::CHECKOUT_SUBMITTED,
            'source' => 'backend',
            'occurred_at' => now()->subMinutes(15 + $idx),
        ]);
    }

    foreach (range(1, 2) as $idx) {
        ConversionEvent::query()->create([
            'event_key' => ConversionEventRegistry::CHECKOUT_COMPLETED,
            'source' => 'backend',
            'occurred_at' => now()->subMinutes(10 + $idx),
        ]);
    }

    foreach (range(1, 3) as $idx) {
        ConversionEvent::query()->create([
            'event_key' => ConversionEventRegistry::REGISTRATION_SUBMITTED,
            'source' => 'frontend',
            'occurred_at' => now()->subMinutes(20 + $idx),
        ]);
    }

    ConversionEvent::query()->create([
        'event_key' => ConversionEventRegistry::REGISTRATION_COMPLETED,
        'source' => 'backend',
        'occurred_at' => now()->subMinutes(5),
    ]);

    $this->actingAs($admin)
        ->get('/admin/ceo-dashboard?days=30')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('dashboard.summary.revenue', 8000)
            ->where('dashboard.summary.estimated_profit', 6000)
            ->where('dashboard.summary.estimated_margin_rate', 75)
            ->where('dashboard.summary.repeat_customer_rate', 50)
            ->where('dashboard.summary.orders_count', 3)
            ->where('dashboard.summary.ltv', 4000)
            ->where('dashboard.summary.checkout_conversion_rate', 50)
            ->where('dashboard.conversion.registration_conversion_rate', 33.33)
            ->where('dashboard.kpi_insights.revenue.wow.direction', 'up')
            ->where('dashboard.kpi_insights.estimated_profit.rag', 'green')
            ->where('dashboard.kpi_insights.checkout_conversion_rate.trend', 'up')
            ->where('dashboard.top_products.0.product_name', 'Kovászos cipó')
            ->has('dashboard.order_profit_trend.points'));
});

it('daily ceo executive report command sends email to configured recipients', function (): void {
    Mail::fake();

    config()->set('analytics.executive_report.recipients', ['ceo@hajnalhej.hu']);

    $admin = User::factory()->admin()->create();
    Order::factory()->create([
        'user_id' => $admin->id,
        'customer_email' => $admin->email,
        'status' => Order::STATUS_COMPLETED,
        'placed_at' => now()->subHours(3),
        'subtotal' => 2000,
        'total' => 2000,
    ]);

    $this->artisan('report:ceo-executive --days=30')
        ->assertSuccessful();

    Mail::assertSent(CeoExecutiveReportMail::class, function (CeoExecutiveReportMail $mail): bool {
        return $mail->hasTo('ceo@hajnalhej.hu');
    });
});
