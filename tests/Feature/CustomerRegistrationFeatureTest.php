<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('guest can open register page', function (): void {
    $response = $this->get('/register');

    $response->assertOk();
});

it('customer can register with valid payload', function (): void {
    Notification::fake();

    $payload = [
        'name' => 'Kiss Anna',
        'email' => 'anna@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ];

    $response = $this->post('/register', $payload);

    $user = User::query()->where('email', 'anna@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user?->role)->toBe(User::ROLE_CUSTOMER);
    expect($user?->email_verified_at)->toBeNull();
    expect(Hash::check('SecurePass123!', (string) $user?->password))->toBeTrue();

    $response->assertRedirect('/email/verify');
    $this->assertAuthenticatedAs($user);

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('duplicate email is not allowed during registration', function (): void {
    User::factory()->create(['email' => 'dupe@example.com']);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Duplikalt Felhasznalo',
        'email' => 'dupe@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ]);

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors(['email']);

    expect(User::query()->where('email', 'dupe@example.com')->count())->toBe(1);
});

it('authenticated user cannot access register page', function (): void {
    $customer = User::factory()->customer()->create();

    $response = $this->actingAs($customer)->get('/register');

    $response->assertRedirect('/account');
});

it('customer cannot access admin pages', function (): void {
    $customer = User::factory()->customer()->create();

    $response = $this->actingAs($customer)->get('/admin/dashboard');

    $response->assertRedirect('/account');
});

it('account page is available for authenticated customer', function (): void {
    $customer = User::factory()->customer()->unverified()->create();

    $response = $this->actingAs($customer)->get('/account');

    $response->assertOk();
});
