<?php

use App\Data\Products\ProductInlineUpdateData;
use App\Data\Products\ProductStoreData;
use App\Data\Products\ProductUpdateData;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Repositories\ProductRepository;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\Cache\CacheVersionService;
use App\Services\ProductIngredientService;
use App\Services\ProductService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::flush();
});

function productSelectorPayload(array $overrides = []): array
{
    return array_merge([
        'active' => true,
        'fields' => ['id', 'name', 'slug'],
        'has_product_ingredients' => true,
        'locale' => app()->getLocale(),
        'sort' => ['sort_order', 'name'],
        'without_trashed' => true,
    ], $overrides);
}

function createSelectableProduct(array $overrides = []): Product
{
    $product = Product::factory()->create(array_merge([
        'is_active' => true,
        'name' => 'Kovaszos kenyer',
        'slug' => 'kovaszos-kenyer',
        'sort_order' => 1,
    ], $overrides));

    ProductIngredient::factory()->create(['product_id' => $product->id]);

    return $product;
}

it('creates a product selector cache key after the first call', function (): void {
    createSelectableProduct();

    $versions = app(CacheVersionService::class);
    $repository = app(ProductRepository::class);
    $key = CacheKeyService::make(
        CacheNamespaces::SELECTORS_PRODUCTS,
        $versions->get(CacheNamespaces::SELECTORS_PRODUCTS),
        productSelectorPayload(),
    );

    expect(Cache::has($key))->toBeFalse();

    expect($repository->listSelectableActiveProducts())->toHaveCount(1);

    expect(Cache::has($key))->toBeTrue();
});

it('serves product selector from cache with the same version', function (): void {
    createSelectableProduct();

    $repository = app(ProductRepository::class);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, 'from `products`')) {
            $queries[] = $query->sql;
        }
    });

    expect($repository->listSelectableActiveProducts())->toHaveCount(1);
    expect($queries)->toHaveCount(1);

    $queries = [];

    expect($repository->listSelectableActiveProducts())->toHaveCount(1);
    expect($queries)->toHaveCount(0);
});

it('uses the same deterministic key for the same product selector version', function (): void {
    $versions = app(CacheVersionService::class);

    $first = CacheKeyService::make(
        CacheNamespaces::SELECTORS_PRODUCTS,
        $versions->get(CacheNamespaces::SELECTORS_PRODUCTS),
        productSelectorPayload(),
    );
    $second = CacheKeyService::make(
        CacheNamespaces::SELECTORS_PRODUCTS,
        $versions->get(CacheNamespaces::SELECTORS_PRODUCTS),
        productSelectorPayload(),
    );

    expect($first)->toBe($second);
});

it('uses a different key for a different product selector payload', function (): void {
    $versions = app(CacheVersionService::class);
    $version = $versions->get(CacheNamespaces::SELECTORS_PRODUCTS);

    $first = CacheKeyService::make(CacheNamespaces::SELECTORS_PRODUCTS, $version, productSelectorPayload());
    $second = CacheKeyService::make(CacheNamespaces::SELECTORS_PRODUCTS, $version, productSelectorPayload([
        'active' => false,
    ]));

    expect($first)->not->toBe($second);
});

it('uses a new product selector key after version bump', function (): void {
    createSelectableProduct();

    $versions = app(CacheVersionService::class);
    $repository = app(ProductRepository::class);
    $firstKey = CacheKeyService::make(
        CacheNamespaces::SELECTORS_PRODUCTS,
        $versions->get(CacheNamespaces::SELECTORS_PRODUCTS),
        productSelectorPayload(),
    );

    $repository->listSelectableActiveProducts();

    expect(Cache::has($firstKey))->toBeTrue();

    $versions->bump(CacheNamespaces::SELECTORS_PRODUCTS);

    $secondKey = CacheKeyService::make(
        CacheNamespaces::SELECTORS_PRODUCTS,
        $versions->get(CacheNamespaces::SELECTORS_PRODUCTS),
        productSelectorPayload(),
    );
    $repository->listSelectableActiveProducts();

    expect($secondKey)->not->toBe($firstKey)
        ->and(Cache::has($secondKey))->toBeTrue();
});

it('bumps product selector version after create update inline update and delete', function (): void {
    $category = Category::factory()->create(['is_active' => true]);
    $service = app(ProductService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_PRODUCTS))->toBe(1);

    $product = $service->store(ProductStoreData::from([
        'category_id' => $category->id,
        'name' => 'Kovaszos kenyer',
        'slug' => 'kovaszos-kenyer',
        'short_description' => null,
        'description' => null,
        'price' => '1290',
        'image_path' => null,
        'sort_order' => 1,
        'is_active' => true,
        'is_featured' => false,
        'stock_status' => Product::STOCK_IN_STOCK,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_PRODUCTS))->toBe(2);

    $service->update($product, ProductUpdateData::from([
        'category_id' => $category->id,
        'name' => 'Magvas kenyer',
        'slug' => 'magvas-kenyer',
        'short_description' => null,
        'description' => null,
        'price' => '1490',
        'image_path' => null,
        'sort_order' => 2,
        'is_active' => true,
        'is_featured' => false,
        'stock_status' => Product::STOCK_IN_STOCK,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_PRODUCTS))->toBe(3);

    $service->updateInline($product->refresh(), ProductInlineUpdateData::from([
        'field' => 'category_id',
        'value' => $category->id,
    ]));

    expect($versions->get(CacheNamespaces::SELECTORS_PRODUCTS))->toBe(4);

    $service->delete($product->refresh());

    expect($versions->get(CacheNamespaces::SELECTORS_PRODUCTS))->toBe(5);
});

it('bumps product selector version after product ingredient create and delete', function (): void {
    $product = Product::factory()->create(['is_active' => true]);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);
    $service = app(ProductIngredientService::class);
    $versions = app(CacheVersionService::class);

    expect($versions->get(CacheNamespaces::SELECTORS_PRODUCTS))->toBe(1);

    $productIngredient = $service->create($product, [
        'ingredient_id' => $ingredient->id,
        'quantity' => '1.5',
        'sort_order' => 1,
        'notes' => null,
    ]);

    expect($versions->get(CacheNamespaces::SELECTORS_PRODUCTS))->toBe(2);

    $service->delete($productIngredient);

    expect($versions->get(CacheNamespaces::SELECTORS_PRODUCTS))->toBe(3);
});

it('returns fresh product selector data after invalidation', function (): void {
    $product = createSelectableProduct();
    $service = app(ProductService::class);
    $repository = app(ProductRepository::class);

    expect($repository->listSelectableActiveProducts()->first()['name'])->toBe('Kovaszos kenyer');

    $service->update($product, ProductUpdateData::from([
        'category_id' => $product->category_id,
        'name' => 'Magvas kenyer',
        'slug' => 'magvas-kenyer',
        'short_description' => $product->short_description,
        'description' => $product->description,
        'price' => $product->price,
        'image_path' => $product->image_path,
        'sort_order' => $product->sort_order,
        'is_active' => true,
        'is_featured' => $product->is_featured,
        'stock_status' => $product->stock_status,
    ]));

    expect($repository->listSelectableActiveProducts()->first()['name'])->toBe('Magvas kenyer');

    $service->delete($product->refresh());

    expect($repository->listSelectableActiveProducts())->toHaveCount(0);
});
