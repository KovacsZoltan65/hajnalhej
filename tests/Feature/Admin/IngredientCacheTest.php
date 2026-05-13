<?php

use App\Data\Ingredients\IngredientStoreData;
use App\Data\Ingredients\IngredientUpdateData;
use App\Models\Ingredient;
use App\Repositories\IngredientRepository;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\Cache\CacheVersionService;
use App\Services\IngredientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::flush();
});

it('creates an ingredient selector cache key after the first call', function (): void {
    Ingredient::factory()->create([
        'name' => 'Buzaliszt',
        'is_active' => true,
    ]);

    $versions = app(CacheVersionService::class);
    $repository = app(IngredientRepository::class);
    $key = CacheKeyService::make(CacheNamespaces::SELECTORS_INGREDIENTS, $versions->get(CacheNamespaces::SELECTORS_INGREDIENTS), [
        'locale' => app()->getLocale(),
    ]);

    expect(Cache::has($key))->toBeFalse();

    expect($repository->listSelectableActive())->toHaveCount(1);

    expect(Cache::has($key))->toBeTrue();
});

it('serves ingredient selector from cache with the same version', function (): void {
    Ingredient::factory()->create([
        'name' => 'Buzaliszt',
        'is_active' => true,
    ]);

    $repository = app(IngredientRepository::class);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, 'from `ingredients`')) {
            $queries[] = $query->sql;
        }
    });

    expect($repository->listSelectableActive())->toHaveCount(1);
    expect($queries)->toHaveCount(1);

    $queries = [];

    expect($repository->listSelectableActive())->toHaveCount(1);
    expect($queries)->toHaveCount(0);
});

it('bumps ingredient selector version after create', function (): void {
    $service = app(IngredientService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_INGREDIENTS))->toBe(1);

    $service->create(IngredientStoreData::from([
        'name' => 'Buzaliszt',
        'slug' => 'buzaliszt',
        'sku' => null,
        'unit' => 'kg',
        'estimated_unit_cost' => '450',
        'current_stock' => '10',
        'minimum_stock' => '2',
        'is_active' => true,
        'notes' => null,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_INGREDIENTS))->toBe(2);
});

it('bumps ingredient selector version after update', function (): void {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Buzaliszt',
        'slug' => 'buzaliszt',
        'unit' => 'kg',
        'is_active' => true,
    ]);
    $service = app(IngredientService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_INGREDIENTS))->toBe(1);

    $service->update($ingredient, IngredientUpdateData::from([
        'name' => 'Rozsliszt',
        'slug' => 'rozsliszt',
        'sku' => $ingredient->sku,
        'unit' => 'kg',
        'estimated_unit_cost' => '520',
        'current_stock' => '12',
        'minimum_stock' => '3',
        'is_active' => true,
        'notes' => $ingredient->notes,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_INGREDIENTS))->toBe(2);
});

it('bumps ingredient selector version after delete', function (): void {
    $ingredient = Ingredient::factory()->create(['is_active' => true]);
    $service = app(IngredientService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_INGREDIENTS))->toBe(1);

    $service->delete($ingredient);

    expect($versions->get(CacheNamespaces::SELECTORS_INGREDIENTS))->toBe(2);
});

it('returns fresh ingredient selector data after invalidation', function (): void {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Buzaliszt',
        'slug' => 'buzaliszt',
        'unit' => 'kg',
        'current_stock' => '10.000',
        'minimum_stock' => '2.000',
        'is_active' => true,
    ]);

    $repository = app(IngredientRepository::class);
    $service = app(IngredientService::class);

    expect($repository->listSelectableActive()->first()['name'])->toBe('Buzaliszt');

    $service->update($ingredient, IngredientUpdateData::from([
        'name' => 'Rozsliszt',
        'slug' => 'rozsliszt',
        'sku' => $ingredient->sku,
        'unit' => 'kg',
        'estimated_unit_cost' => $ingredient->estimated_unit_cost,
        'current_stock' => '12',
        'minimum_stock' => '3',
        'is_active' => true,
        'notes' => $ingredient->notes,
    ]));

    expect($repository->listSelectableActive()->first()['name'])->toBe('Rozsliszt');

    $service->delete($ingredient->refresh());

    expect($repository->listSelectableActive())->toHaveCount(0);
});
