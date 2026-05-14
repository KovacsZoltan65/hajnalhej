<?php

use App\Data\Suppliers\SupplierStoreData;
use App\Data\Suppliers\SupplierUpdateData;
use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\Cache\CacheVersionService;
use App\Services\SupplierService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::flush();
});

it('creates a supplier selector cache key after the first call', function (): void {
    Supplier::factory()->create(['name' => 'Malom Kft.']);

    $versions = app(CacheVersionService::class);
    $repository = app(SupplierRepository::class);
    $key = CacheKeyService::make(CacheNamespaces::SELECTORS_SUPPLIERS, $versions->get(CacheNamespaces::SELECTORS_SUPPLIERS), [
        'active' => null,
        'locale' => app()->getLocale(),
    ]);

    expect(Cache::has($key))->toBeFalse();

    expect($repository->listSelectable())->toHaveCount(1);

    expect(Cache::has($key))->toBeTrue();
});

it('serves supplier selector from cache with the same version', function (): void {
    Supplier::factory()->create(['name' => 'Malom Kft.']);

    $repository = app(SupplierRepository::class);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, 'from `suppliers`')) {
            $queries[] = $query->sql;
        }
    });

    expect($repository->listSelectable())->toHaveCount(1);
    expect($queries)->toHaveCount(1);

    $queries = [];

    expect($repository->listSelectable())->toHaveCount(1);
    expect($queries)->toHaveCount(0);
});

it('uses the same deterministic key for the same supplier selector version', function (): void {
    $versions = app(CacheVersionService::class);

    $payload = [
        'active' => true,
        'locale' => app()->getLocale(),
    ];

    $first = CacheKeyService::make(CacheNamespaces::SELECTORS_SUPPLIERS, $versions->get(CacheNamespaces::SELECTORS_SUPPLIERS), $payload);
    $second = CacheKeyService::make(CacheNamespaces::SELECTORS_SUPPLIERS, $versions->get(CacheNamespaces::SELECTORS_SUPPLIERS), $payload);

    expect($first)->toBe($second);
});

it('uses a new supplier selector key after version bump', function (): void {
    Supplier::factory()->create(['name' => 'Malom Kft.', 'active' => true]);

    $versions = app(CacheVersionService::class);
    $repository = app(SupplierRepository::class);
    $payload = [
        'active' => true,
        'locale' => app()->getLocale(),
    ];

    $firstKey = CacheKeyService::make(CacheNamespaces::SELECTORS_SUPPLIERS, $versions->get(CacheNamespaces::SELECTORS_SUPPLIERS), $payload);
    $repository->listSelectable(active: true);

    expect(Cache::has($firstKey))->toBeTrue();

    $versions->bump(CacheNamespaces::SELECTORS_SUPPLIERS);

    $secondKey = CacheKeyService::make(CacheNamespaces::SELECTORS_SUPPLIERS, $versions->get(CacheNamespaces::SELECTORS_SUPPLIERS), $payload);
    $repository->listSelectable(active: true);

    expect($secondKey)->not->toBe($firstKey)
        ->and(Cache::has($secondKey))->toBeTrue();
});

it('bumps supplier selector version after create', function (): void {
    $service = app(SupplierService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_SUPPLIERS))->toBe(1);

    $service->create(SupplierStoreData::from([
        'name' => 'Malom Kft.',
        'email' => 'malom@example.test',
        'phone' => null,
        'tax_number' => null,
        'lead_time_days' => 4,
        'notes' => null,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_SUPPLIERS))->toBe(2);
});

it('bumps supplier selector version after update', function (): void {
    $supplier = Supplier::factory()->create(['name' => 'Regi Malom']);
    $service = app(SupplierService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_SUPPLIERS))->toBe(1);

    $service->update($supplier, SupplierUpdateData::from([
        'name' => 'Uj Malom',
        'email' => null,
        'phone' => null,
        'tax_number' => null,
        'lead_time_days' => 5,
        'notes' => null,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_SUPPLIERS))->toBe(2);
});

it('bumps supplier selector version after delete', function (): void {
    $supplier = Supplier::factory()->create();
    $service = app(SupplierService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_SUPPLIERS))->toBe(1);

    $service->delete($supplier);

    expect($versions->get(CacheNamespaces::SELECTORS_SUPPLIERS))->toBe(2);
});

it('returns fresh supplier selector data after invalidation', function (): void {
    $supplier = Supplier::factory()->create([
        'name' => 'Regi Malom',
        'active' => true,
    ]);

    $repository = app(SupplierRepository::class);
    $service = app(SupplierService::class);

    expect($repository->listSelectable(active: true)->first()->name)->toBe('Regi Malom');

    $service->update($supplier, SupplierUpdateData::from([
        'name' => 'Uj Malom',
        'email' => $supplier->email,
        'phone' => $supplier->phone,
        'tax_number' => $supplier->tax_number,
        'lead_time_days' => $supplier->lead_time_days,
        'notes' => $supplier->notes,
    ]));

    expect($repository->listSelectable(active: true)->first()->name)->toBe('Uj Malom');

    $service->delete($supplier->refresh());

    expect($repository->listSelectable(active: true))->toHaveCount(0);
});
