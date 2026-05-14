<?php

use App\Data\Categories\CategoryStoreData;
use App\Data\Categories\CategoryUpdateData;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\Cache\CacheNamespaces;
use App\Services\Cache\CacheVersionService;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::flush();
});

it('serves category selector from cache after the first call', function (): void {
    Category::factory()->create([
        'name' => 'Kenyerek',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $repository = app(CategoryRepository::class);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, 'from `categories`')) {
            $queries[] = $query->sql;
        }
    });

    expect($repository->listSelectable())->toHaveCount(1);
    expect($queries)->toHaveCount(1);

    $queries = [];

    expect($repository->listSelectable())->toHaveCount(1);
    expect($queries)->toHaveCount(0);
});

it('bumps category selector version after create update and delete', function (): void {
    $service = app(CategoryService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_CATEGORIES))->toBe(1);

    $category = $service->create(CategoryStoreData::from([
        'name' => 'Kenyerek',
        'slug' => 'kenyerek',
        'description' => null,
        'is_active' => true,
        'sort_order' => 1,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_CATEGORIES))->toBe(2);

    $service->update($category, CategoryUpdateData::from([
        'name' => 'Kovaszos kenyerek',
        'slug' => 'kovaszos-kenyerek',
        'description' => null,
        'is_active' => true,
        'sort_order' => 1,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_CATEGORIES))->toBe(3);

    $service->delete($category->refresh());

    expect($versions->get(CacheNamespaces::SELECTORS_CATEGORIES))->toBe(4);
});

it('returns fresh category selector data after invalidation', function (): void {
    $category = Category::factory()->create([
        'name' => 'Kenyerek',
        'slug' => 'kenyerek',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $repository = app(CategoryRepository::class);
    $service = app(CategoryService::class);

    expect($repository->listSelectable()->first()['name'])->toBe('Kenyerek');

    $service->update($category, CategoryUpdateData::from([
        'name' => 'Kovaszos kenyerek',
        'slug' => 'kovaszos-kenyerek',
        'description' => $category->description,
        'is_active' => true,
        'sort_order' => 1,
    ]));

    expect($repository->listSelectable()->first()['name'])->toBe('Kovaszos kenyerek');

    $service->delete($category->refresh());

    expect($repository->listSelectable())->toHaveCount(0);
});
