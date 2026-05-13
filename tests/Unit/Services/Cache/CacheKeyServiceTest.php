<?php

use App\Services\Cache\CacheKeyService;

it('returns the same hash for the same payload', function (): void {
    $payload = ['active' => true, 'locale' => 'hu'];

    expect(CacheKeyService::stableHash($payload))
        ->toBe(CacheKeyService::stableHash($payload));
});

it('returns the same hash for different key order', function (): void {
    $first = [
        'filters' => [
            'active' => true,
            'locale' => 'hu',
        ],
        'page' => 1,
    ];

    $second = [
        'page' => 1,
        'filters' => [
            'locale' => 'hu',
            'active' => true,
        ],
    ];

    expect(CacheKeyService::stableHash($first))
        ->toBe(CacheKeyService::stableHash($second));
});

it('returns a different hash for different payloads', function (): void {
    expect(CacheKeyService::stableHash(['active' => true]))
        ->not->toBe(CacheKeyService::stableHash(['active' => false]));
});

it('builds a versioned namespace key', function (): void {
    $key = CacheKeyService::make('selectors.categories', 3, ['locale' => 'hu']);

    expect($key)
        ->toStartWith('selectors.categories:v3:')
        ->and($key)->toMatch('/^selectors\.categories:v3:[a-f0-9]{12}$/');
});
