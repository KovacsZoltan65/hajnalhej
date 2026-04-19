<?php

use App\Models\User;

it('home page loads', function (): void {
    $response = $this->get('/');

    $response->assertOk();
});

it('login page loads', function (): void {
    $response = $this->get('/login');

    $response->assertOk();
});

it('admin dashboard requires authentication', function (): void {
    $response = $this->get('/admin/dashboard');

    $response->assertRedirect('/login');
});

it('authenticated admin can access dashboard', function (): void {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertOk();
});
