<?php

use App\Models\User;
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
            ->has('analytics.summary'));
});

it('customer cannot access conversion analytics dashboard', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/conversion-analytics')
        ->assertForbidden();
});

