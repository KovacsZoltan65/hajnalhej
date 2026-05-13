<?php

use App\Services\Cache\CacheVersionService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Cache::setDefaultDriver('array');
    Cache::flush();
});

it('returns version one for a missing namespace', function (): void {
    $versions = app(CacheVersionService::class);

    expect($versions->get('selectors.categories'))->toBe(1);
});

it('bumps a namespace version', function (): void {
    $versions = app(CacheVersionService::class);

    expect($versions->bump('selectors.categories'))->toBe(2)
        ->and($versions->get('selectors.categories'))->toBe(2);
});

it('keeps the correct version after multiple bumps', function (): void {
    $versions = app(CacheVersionService::class);

    $versions->bump('selectors.categories');
    $versions->bump('selectors.categories');
    $versions->bump('selectors.categories');

    expect($versions->get('selectors.categories'))->toBe(4);
});

it('resets a namespace back to version one', function (): void {
    $versions = app(CacheVersionService::class);

    $versions->bump('selectors.categories');
    $versions->reset('selectors.categories');

    expect($versions->get('selectors.categories'))->toBe(1);
});
