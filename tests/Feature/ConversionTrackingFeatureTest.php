<?php

use App\Models\ConversionEvent;
use App\Models\Product;
use App\Models\User;
use App\Support\ConversionEventRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('stores frontend cta click conversion event', function (): void {
    $response = $this->postJson('/conversion-events', [
        'event_key' => ConversionEventRegistry::CTA_CLICK,
        'funnel' => 'landing',
        'step' => 'click',
        'cta_id' => 'hero.register_primary',
        'metadata' => [
            'href' => '/register',
        ],
    ]);

    $response->assertOk()->assertJson(['status' => 'ok']);

    $event = ConversionEvent::query()->latest('id')->first();

    expect($event)->not->toBeNull();
    expect($event?->event_key)->toBe(ConversionEventRegistry::CTA_CLICK);
    expect($event?->source)->toBe('frontend');
    expect($event?->funnel)->toBe('landing');
});

it('home visit logs hero viewed conversion event with ab variant', function (): void {
    $this->get('/')->assertOk();

    $event = ConversionEvent::query()
        ->where('event_key', ConversionEventRegistry::HERO_VIEWED)
        ->latest('id')
        ->first();

    expect($event)->not->toBeNull();
    expect($event?->hero_variant)->not->toBeNull();
});

it('cart funnel backend events are stored', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 1800,
    ]);

    $this->post('/cart/items', [
        'product_id' => $product->id,
        'quantity' => 1,
    ])->assertRedirect();

    $this->patch("/cart/items/{$product->id}", [
        'quantity' => 3,
    ])->assertRedirect();

    $this->delete("/cart/items/{$product->id}")->assertRedirect();
    $this->delete('/cart')->assertRedirect();

    $keys = ConversionEvent::query()->pluck('event_key')->all();

    expect($keys)->toContain(ConversionEventRegistry::CART_ITEM_ADDED);
    expect($keys)->toContain(ConversionEventRegistry::CART_ITEM_UPDATED);
    expect($keys)->toContain(ConversionEventRegistry::CART_ITEM_REMOVED);
    expect($keys)->toContain(ConversionEventRegistry::CART_CLEARED);
});

it('checkout completion logs conversion event', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 2000,
    ]);

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ])->post('/checkout', [
        'customer_name' => 'Teszt Elek',
        'customer_email' => 'teszt@hajnalhej.hu',
        'customer_phone' => '+36123456789',
        'pickup_date' => now()->addDay()->toDateString(),
        'pickup_time_slot' => '08:00-10:00',
        'accept_privacy' => true,
        'accept_terms' => true,
    ]);

    $response->assertRedirect();

    $event = ConversionEvent::query()
        ->where('event_key', ConversionEventRegistry::CHECKOUT_COMPLETED)
        ->latest('id')
        ->first();

    expect($event)->not->toBeNull();
    expect((float) ($event?->metadata['total'] ?? 0))->toBe(4000.0);
});

it('successful registration logs conversion completion event', function (): void {
    Notification::fake();

    $response = $this->post('/register', [
        'name' => 'Kiss Anna',
        'email' => 'kiss.anna@hajnalhej.hu',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ]);

    $response->assertRedirect('/email/verify');

    $user = User::query()->where('email', 'kiss.anna@hajnalhej.hu')->first();
    expect($user)->not->toBeNull();

    $event = ConversionEvent::query()
        ->where('event_key', ConversionEventRegistry::REGISTRATION_COMPLETED)
        ->where('user_id', $user?->id)
        ->latest('id')
        ->first();

    expect($event)->not->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});

