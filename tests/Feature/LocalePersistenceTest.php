<?php

declare(strict_types=1);

use App\Models\User;

it('prioritizes authenticated user locale', function (): void {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)
        ->withSession(['locale' => 'hu'])
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('locale', 'en'));
});

it('uses session locale as guest fallback', function (): void {
    $this->withSession(['locale' => 'hu'])
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('locale', 'hu'));
});

it('detects locale from accept language', function (): void {
    $this->withHeader('Accept-Language', 'hu-HU,hu;q=0.9,en;q=0.7')
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('locale', 'hu'));
});

it('uses request locale when higher priority storage is absent', function (): void {
    $this->get('/?locale=en')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('locale', 'en'));
});

it('falls back for invalid locale input', function (): void {
    config(['app.locale' => 'en']);

    $this->withSession(['locale' => 'xx'])
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('locale', 'en'));
});

it('switches guest locale manually', function (): void {
    $this->from('/')
        ->post(route('locale.switch'), ['locale' => 'hu'])
        ->assertRedirect('/');

    $this->assertSame('hu', session('locale'));
});

it('switches guest locale manually without an inertia redirect when json is requested', function (): void {
    $this->postJson(route('locale.switch'), ['locale' => 'en'])
        ->assertOk()
        ->assertJsonPath('locale', 'en');

    $this->assertSame('en', session('locale'));
});

it('switches authenticated user locale manually', function (): void {
    $user = User::factory()->create(['locale' => null]);

    $this->actingAs($user)
        ->from('/')
        ->post(route('locale.switch'), ['locale' => 'en'])
        ->assertRedirect('/');

    expect($user->refresh()->locale)->toBe('en');
});

it('persists detected guest locale between requests', function (): void {
    $this->withHeader('Accept-Language', 'hu-HU,hu;q=0.9')
        ->get('/')
        ->assertOk();

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('locale', 'hu'));
});
