<?php

use App\Data\Branches\BranchStoreData;
use App\Data\Branches\BranchType;
use App\Data\Branches\BranchUpdateData;
use App\Models\Branch;
use App\Repositories\BranchRepository;
use App\Services\BranchService;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\Cache\CacheVersionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::flush();
});

function branchSelectorPayload(): array
{
    return [
        'active' => true,
        'locale' => app()->getLocale(),
        'types' => [
            BranchType::BAKERY,
            BranchType::SHOP,
            BranchType::PICKUP_POINT,
        ],
    ];
}

it('creates a branch selector cache key after the first call', function (): void {
    Branch::factory()->create([
        'name' => 'Hajnalhej Bolt',
        'type' => BranchType::SHOP,
        'active' => true,
    ]);

    $versions = app(CacheVersionService::class);
    $repository = app(BranchRepository::class);
    $key = CacheKeyService::make(
        CacheNamespaces::SELECTORS_BRANCHES,
        $versions->get(CacheNamespaces::SELECTORS_BRANCHES),
        branchSelectorPayload(),
    );

    expect(Cache::has($key))->toBeFalse();

    expect($repository->activePickupOptions())->toHaveCount(1);

    expect(Cache::has($key))->toBeTrue();
});

it('serves branch selector from cache with the same version', function (): void {
    Branch::factory()->create([
        'name' => 'Hajnalhej Bolt',
        'type' => BranchType::SHOP,
        'active' => true,
    ]);

    $repository = app(BranchRepository::class);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, 'from `branches`')) {
            $queries[] = $query->sql;
        }
    });

    expect($repository->activePickupOptions())->toHaveCount(1);
    expect($queries)->toHaveCount(1);

    $queries = [];

    expect($repository->activePickupOptions())->toHaveCount(1);
    expect($queries)->toHaveCount(0);
});

it('uses the same deterministic key for the same branch selector version', function (): void {
    $versions = app(CacheVersionService::class);

    $first = CacheKeyService::make(
        CacheNamespaces::SELECTORS_BRANCHES,
        $versions->get(CacheNamespaces::SELECTORS_BRANCHES),
        branchSelectorPayload(),
    );
    $second = CacheKeyService::make(
        CacheNamespaces::SELECTORS_BRANCHES,
        $versions->get(CacheNamespaces::SELECTORS_BRANCHES),
        branchSelectorPayload(),
    );

    expect($first)->toBe($second);
});

it('uses a new branch selector key after version bump', function (): void {
    Branch::factory()->create([
        'name' => 'Hajnalhej Bolt',
        'type' => BranchType::SHOP,
        'active' => true,
    ]);

    $versions = app(CacheVersionService::class);
    $repository = app(BranchRepository::class);
    $firstKey = CacheKeyService::make(
        CacheNamespaces::SELECTORS_BRANCHES,
        $versions->get(CacheNamespaces::SELECTORS_BRANCHES),
        branchSelectorPayload(),
    );

    $repository->activePickupOptions();

    expect(Cache::has($firstKey))->toBeTrue();

    $versions->bump(CacheNamespaces::SELECTORS_BRANCHES);

    $secondKey = CacheKeyService::make(
        CacheNamespaces::SELECTORS_BRANCHES,
        $versions->get(CacheNamespaces::SELECTORS_BRANCHES),
        branchSelectorPayload(),
    );
    $repository->activePickupOptions();

    expect($secondKey)->not->toBe($firstKey)
        ->and(Cache::has($secondKey))->toBeTrue();
});

it('bumps branch selector version after create', function (): void {
    $service = app(BranchService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_BRANCHES))->toBe(1);

    $service->create(BranchStoreData::from([
        'name' => 'Hajnalhej Bolt',
        'code' => 'SHOP-01',
        'type' => BranchType::SHOP,
        'email' => null,
        'phone' => null,
        'address' => 'Budapest, Reggel ter 1.',
        'active' => true,
        'meta' => null,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_BRANCHES))->toBe(2);
});

it('bumps branch selector version after update', function (): void {
    $branch = Branch::factory()->create([
        'name' => 'Regi Bolt',
        'code' => 'SHOP-OLD',
        'type' => BranchType::SHOP,
        'active' => true,
    ]);
    $service = app(BranchService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_BRANCHES))->toBe(1);

    $service->update($branch, BranchUpdateData::from([
        'name' => 'Uj Bolt',
        'code' => 'SHOP-NEW',
        'type' => BranchType::SHOP,
        'email' => $branch->email,
        'phone' => $branch->phone,
        'address' => $branch->address,
        'active' => true,
        'meta' => $branch->meta,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_BRANCHES))->toBe(2);
});

it('bumps branch selector version after delete', function (): void {
    $branch = Branch::factory()->create([
        'type' => BranchType::SHOP,
        'active' => true,
    ]);
    $service = app(BranchService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_BRANCHES))->toBe(1);

    $service->delete($branch);

    expect($versions->get(CacheNamespaces::SELECTORS_BRANCHES))->toBe(2);
});

it('returns fresh branch selector data after invalidation', function (): void {
    $branch = Branch::factory()->create([
        'name' => 'Regi Bolt',
        'code' => 'SHOP-OLD',
        'type' => BranchType::SHOP,
        'active' => true,
    ]);

    $repository = app(BranchRepository::class);
    $service = app(BranchService::class);

    expect($repository->activePickupOptions()->first()->name)->toBe('Regi Bolt');

    $service->update($branch, BranchUpdateData::from([
        'name' => 'Uj Bolt',
        'code' => 'SHOP-NEW',
        'type' => BranchType::SHOP,
        'email' => $branch->email,
        'phone' => $branch->phone,
        'address' => $branch->address,
        'active' => true,
        'meta' => $branch->meta,
    ]));

    expect($repository->activePickupOptions()->first()->name)->toBe('Uj Bolt');

    $service->delete($branch->refresh());

    expect($repository->activePickupOptions())->toHaveCount(0);
});
