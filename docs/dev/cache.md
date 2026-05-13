# Cache architecture

Hajnalhej uses versioned cache namespaces for shared selector caches. We avoid Redis `KEYS` and pattern deletion in production because those operations can block Redis on large keyspaces.

## Versioned keys

Selector keys include the namespace version:

```text
selectors.categories:v1:<hash>
selectors.categories:v2:<hash>
```

When source data changes, the namespace version is bumped. Old keys can expire naturally, so invalidation does not need physical pattern deletion.

## Adding a namespace

Add the constant to `App\Services\Cache\CacheNamespaces`, then use `CacheVersionService` and `CacheKeyService` when building the cache key.

Only use a namespace in production code once a real cache read is introduced for it.

## Invalidating

Use `SelectorCacheInvalidator` from the service layer after successful writes:

```php
$this->selectorCacheInvalidator->categories();
```

## Current cached selectors

- `selectors.categories`: category selector options from `CategoryRepository::listSelectable()`

## Store compatibility

The version value is stored through Laravel cache and works with the database cache store. Redis is also compatible because the architecture does not require key scans or tags.
