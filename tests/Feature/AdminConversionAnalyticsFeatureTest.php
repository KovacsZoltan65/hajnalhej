<?php

use App\Models\User;
use App\Models\Order;
use App\Support\ConversionEventRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('admin can access conversion analytics dashboard', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/conversion-analytics')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ConversionAnalytics/Index')
            ->where('filters.days', 30)
            ->has('analytics.summary')
            ->has('analytics.conversion_rates')
            ->has('analytics.trend.points')
            ->has('analytics.commerce')
            ->has('analytics.commerce_trend.points')
            ->has('analytics.top_product_revenue')
            ->has('analytics.hero_comparison')
            ->has('analytics.drop_off_top'));
});

it('customer cannot access conversion analytics dashboard', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/conversion-analytics')
        ->assertForbidden();
});

it('conversion analytics exposes computed rate and drop-off structures', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->postJson('/conversion-events', [
        'event_key' => ConversionEventRegistry::HERO_VIEWED,
        'funnel' => 'landing',
        'step' => 'hero_view',
        'hero_variant' => 'artisan_story',
    ])->assertOk();

    $this->actingAs($admin)->postJson('/conversion-events', [
        'event_key' => ConversionEventRegistry::CTA_CLICK,
        'funnel' => 'landing',
        'step' => 'click',
        'cta_id' => 'hero.register_primary',
        'hero_variant' => 'artisan_story',
    ])->assertOk();

    $this->actingAs($admin)->get('/admin/conversion-analytics')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('analytics.conversion_rates.0.id', 'hero_to_register')
            ->where('analytics.hero_comparison.0.variant', 'artisan_story')
            ->has('analytics.drop_off_top'));
});

it('conversion analytics exposes revenue aov repeat rate and ltv metrics', function (): void {
    $admin = User::factory()->admin()->create();
    $repeatUser = User::factory()->customer()->create(['email' => 'repeat@hajnalhej.hu']);
    $singleUser = User::factory()->customer()->create(['email' => 'single@hajnalhej.hu']);

    $first = Order::factory()->create([
        'user_id' => $repeatUser->id,
        'customer_email' => $repeatUser->email,
        'total' => 5000,
        'subtotal' => 5000,
        'status' => Order::STATUS_COMPLETED,
        'placed_at' => now()->subDay(),
    ]);
    $first->items()->create([
        'product_id' => null,
        'product_name_snapshot' => 'Kovászos cipó',
        'unit_price' => 2500,
        'quantity' => 2,
        'line_total' => 5000,
    ]);

    $second = Order::factory()->create([
        'user_id' => $repeatUser->id,
        'customer_email' => $repeatUser->email,
        'total' => 7000,
        'subtotal' => 7000,
        'status' => Order::STATUS_COMPLETED,
        'placed_at' => now()->subHours(20),
    ]);
    $second->items()->create([
        'product_id' => null,
        'product_name_snapshot' => 'Kovászos cipó',
        'unit_price' => 3500,
        'quantity' => 2,
        'line_total' => 7000,
    ]);

    $third = Order::factory()->create([
        'user_id' => $singleUser->id,
        'customer_email' => $singleUser->email,
        'total' => 3000,
        'subtotal' => 3000,
        'status' => Order::STATUS_COMPLETED,
        'placed_at' => now()->subHours(6),
    ]);
    $third->items()->create([
        'product_id' => null,
        'product_name_snapshot' => 'Vajas croissant',
        'unit_price' => 1500,
        'quantity' => 2,
        'line_total' => 3000,
    ]);

    $this->actingAs($admin)->get('/admin/conversion-analytics')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('analytics.commerce.revenue_total', 15000)
            ->where('analytics.commerce.orders_count', 3)
            ->where('analytics.commerce.unique_customers', 2)
            ->where('analytics.commerce.average_cart_value', 5000)
            ->where('analytics.commerce.repeat_customers', 1)
            ->where('analytics.commerce.repeat_customer_rate', 50)
            ->where('analytics.commerce.ltv', 7500)
            ->where('analytics.top_product_revenue.0.product_name', 'Kovászos cipó')
            ->where('analytics.top_product_revenue.0.revenue', 12000)
            ->has('analytics.commerce_trend.points'));
});
