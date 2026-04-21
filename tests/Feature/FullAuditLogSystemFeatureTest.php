<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Audit\OrderAuditService;
use App\Services\Audit\UserActivityAuditService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('logs user login and logout activity events', function (): void {
    $customer = User::factory()->customer()->create([
        'password' => 'password',
    ]);

    $this->post('/login', [
        'email' => $customer->email,
        'password' => 'password',
    ])->assertRedirect('/account');

    $loginActivity = Activity::query()
        ->where('event', UserActivityAuditService::USER_LOGIN)
        ->latest('id')
        ->first();

    expect($loginActivity)->not->toBeNull()
        ->and($loginActivity?->log_name)->toBe(UserActivityAuditService::LOG_NAME);

    $this->actingAs($customer)
        ->post('/logout')
        ->assertRedirect('/');

    $logoutActivity = Activity::query()
        ->where('event', UserActivityAuditService::USER_LOGOUT)
        ->latest('id')
        ->first();

    expect($logoutActivity)->not->toBeNull()
        ->and($logoutActivity?->log_name)->toBe(UserActivityAuditService::LOG_NAME);
});

it('logs user registration activity event', function (): void {
    $this->post('/register', [
        'name' => 'Fresh Customer',
        'email' => 'fresh-customer@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect('/email/verify');

    $activity = Activity::query()
        ->where('event', UserActivityAuditService::USER_REGISTERED)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity?->log_name)->toBe(UserActivityAuditService::LOG_NAME)
        ->and((string) data_get($activity?->properties?->toArray() ?? [], 'event_key'))
        ->toBe(UserActivityAuditService::USER_REGISTERED);
});

it('logs email verification event', function (): void {
    $user = User::factory()->customer()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(10), [
        'id' => $user->id,
        'hash' => sha1((string) $user->email),
    ]);

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect('/account');

    $activity = Activity::query()
        ->where('event', UserActivityAuditService::USER_EMAIL_VERIFIED)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity?->log_name)->toBe(UserActivityAuditService::LOG_NAME);
});

it('logs order placement with structured snapshots', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 1200,
    ]);

    $this->post('/cart/items', [
        'product_id' => $product->id,
        'quantity' => 2,
    ])->assertSessionHasNoErrors();

    $this->post('/checkout', [
        'customer_name' => 'Bakery Guest',
        'customer_email' => 'guest@example.test',
        'customer_phone' => '+36123456789',
        'notes' => 'Please prepare fresh.',
        'pickup_date' => now()->addDay()->toDateString(),
        'pickup_time_slot' => '08:00-10:00',
        'accept_privacy' => true,
        'accept_terms' => true,
    ])->assertRedirect();

    $activity = Activity::query()
        ->where('event', OrderAuditService::ORDER_PLACED)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity?->log_name)->toBe(OrderAuditService::LOG_NAME);

    $properties = $activity?->properties?->toArray() ?? [];

    expect(data_get($properties, 'customer_snapshot.is_guest_checkout'))->toBeTrue()
        ->and(data_get($properties, 'items_summary.0.product_id'))->toBe($product->id)
        ->and(data_get($properties, 'totals_snapshot.currency'))->toBe('HUF')
        ->and(data_get($properties, 'pickup_snapshot.pickup_time_slot'))->toBe('08:00-10:00');
});

it('logs order admin status, note and pickup updates', function (): void {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create([
        'status' => Order::STATUS_PENDING,
        'internal_notes' => null,
        'pickup_date' => null,
        'pickup_time_slot' => null,
    ]);

    $this->actingAs($admin)
        ->patch("/admin/orders/{$order->id}/status", [
            'status' => Order::STATUS_CONFIRMED,
            'internal_notes' => 'Customer asked for earlier pickup.',
            'pickup_date' => now()->addDays(2)->toDateString(),
            'pickup_time_slot' => '07:30-09:00',
        ])
        ->assertSessionHasNoErrors();

    expect(Activity::query()->where('event', OrderAuditService::ORDER_STATUS_UPDATED)->exists())->toBeTrue()
        ->and(Activity::query()->where('event', OrderAuditService::ORDER_INTERNAL_NOTE_CREATED)->exists())->toBeTrue()
        ->and(Activity::query()->where('event', OrderAuditService::ORDER_PICKUP_UPDATED)->exists())->toBeTrue();
});

it('filters audit list by domain and subject type', function (): void {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create();

    activity()
        ->useLog(OrderAuditService::LOG_NAME)
        ->causedBy($admin)
        ->performedOn($order)
        ->event(OrderAuditService::ORDER_STATUS_UPDATED)
        ->withProperties([
            'event_key' => OrderAuditService::ORDER_STATUS_UPDATED,
            'before' => ['status' => Order::STATUS_PENDING],
            'after' => ['status' => Order::STATUS_CONFIRMED],
            'context' => ['operation' => 'test.filter'],
        ])
        ->log('Order status updated');

    $this->actingAs($admin)
        ->get('/admin/audit-logs?log_name=orders&subject_type=order')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/AuditLogs/Index')
            ->where('filters.log_name', 'orders')
            ->where('filters.subject_type', 'order')
            ->where('logs.data.0.log_name', 'orders')
            ->where('logs.data.0.subject.type', 'order'));
});
