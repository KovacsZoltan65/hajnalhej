# Cache architecture

Hajnalhej uses versioned cache namespaces for shared selector caches. We avoid Redis `KEYS` and pattern deletion in production because those operations can block Redis on large keyspaces.

## Versioned keys

Selector keys include the namespace version:

```text
selectors.categories:v1:<hash>
selectors.categories:v2:<hash>
selectors.ingredients:v1:<hash>
selectors.suppliers:v1:<hash>
selectors.branches:v1:<hash>
selectors.products:v1:<hash>
```

When source data changes, the namespace version is bumped. Old keys can expire naturally, so invalidation does not need physical pattern deletion.

Selector cache entries use a 30 minute TTL. Namespace version values are long-lived cache entries.

## Adding a namespace

Add the constant to `App\Services\Cache\CacheNamespaces`, then use `CacheVersionService` and `CacheKeyService` when building the cache key.

Only use a namespace in production code once a real cache read is introduced for it.

## Invalidating

Use `SelectorCacheInvalidator` from the service layer after successful writes:

```php
$this->selectorCacheInvalidator->categories();
$this->selectorCacheInvalidator->ingredients();
$this->selectorCacheInvalidator->suppliers();
$this->selectorCacheInvalidator->branches();
$this->selectorCacheInvalidator->products();
```

## Current cached selectors

- `selectors.categories`: category selector options from `CategoryRepository::listSelectable()`
- `selectors.ingredients`: active ingredient selector options from `IngredientRepository::listSelectableActive()`
- `selectors.suppliers`: supplier selector options from `SupplierRepository::listSelectable()`
- `selectors.branches`: active pickup branch selector options from `BranchRepository::activePickupOptions()`
- `selectors.products`: active product selector options with at least one product ingredient from `ProductRepository::listSelectableActiveProducts()`

The category selector is invalidated by `CategoryService` after category create, update, and delete.
The ingredient selector is invalidated by `IngredientService` after ingredient create, update, inline update, and delete. `InventoryService` also invalidates it after stock movements because the selector exposes `current_stock`, `minimum_stock`, and `is_low_stock`.
The supplier selector is invalidated by `SupplierService` after supplier create, update, and delete.
The branch selector is invalidated by `BranchService` after branch create, update, and delete.
The product selector is invalidated by `ProductService` after product create, update, inline update, and delete. `ProductIngredientService` also invalidates it after product ingredient create and delete because the selector requires `whereHas('productIngredients')`.

Product selector cache payload includes locale, active-only filtering, the `has_product_ingredients` requirement, selected fields, sort order, and soft delete exclusion.

## Store compatibility

The version value is stored through Laravel cache and works with the database cache store. Redis is also compatible because the architecture does not require key scans or tags.
