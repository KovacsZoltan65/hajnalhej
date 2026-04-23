<?php

use App\Models\User;
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
