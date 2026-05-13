<?php

declare(strict_types=1);

namespace App\Services\Cache;

class SelectorCacheInvalidator
{
    public function __construct(private readonly CacheVersionService $versions) {}

    public function categories(): void
    {
        $this->versions->bump(CacheNamespaces::SELECTORS_CATEGORIES);
    }

    public function products(): void
    {
        $this->versions->bump(CacheNamespaces::SELECTORS_PRODUCTS);
    }

    public function ingredients(): void
    {
        $this->versions->bump(CacheNamespaces::SELECTORS_INGREDIENTS);
    }

    public function suppliers(): void
    {
        $this->versions->bump(CacheNamespaces::SELECTORS_SUPPLIERS);
    }

    public function branches(): void
    {
        $this->versions->bump(CacheNamespaces::SELECTORS_BRANCHES);
    }
}
