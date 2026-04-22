<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('products index auth nelkul tiltott', function (): void {
    $response = $this->get('/admin/products');

    $response->assertRedirect('/login');
});

it('products index auth-val elerheto', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/products');

    $response->assertOk();
});

it('product create valid adatokkal mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->post('/admin/products', [
        'category_id' => $category->id,
        'name' => 'Klasszikus kovaszos kenyer',
        'slug' => 'klasszikus-kovaszos-kenyer',
        'short_description' => 'Friss, ropogos',
        'description' => 'Lassu fermentacio',
        'price' => 2450.00,
        'is_active' => true,
        'is_featured' => false,
        'stock_status' => 'in_stock',
        'image_path' => null,
        'sort_order' => 1,
    ]);

    $response->assertRedirect('/admin/products');

    $this->assertDatabaseHas('products', [
        'name' => 'Klasszikus kovaszos kenyer',
        'slug' => 'klasszikus-kovaszos-kenyer',
        'category_id' => $category->id,
    ]);
});

it('product create elfogadja a frontend altal generalt slugot', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->post('/admin/products', [
        'category_id' => $category->id,
        'name' => 'Kovaszos Bagett Special',
        'slug' => 'kovaszos-bagett-special',
        'short_description' => 'Friss bagett',
        'description' => 'Kora reggeli sutessel.',
        'price' => 1290,
        'is_active' => true,
        'is_featured' => false,
        'stock_status' => 'in_stock',
        'image_path' => 'products/kovaszos-bagett.jpg',
        'sort_order' => 2,
    ]);

    $response->assertRedirect('/admin/products');

    $this->assertDatabaseHas('products', [
        'name' => 'Kovaszos Bagett Special',
        'slug' => 'kovaszos-bagett-special',
        'category_id' => $category->id,
    ]);
});

it('product slug backend oldalon normalizalodik es egyedi lesz', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Elso termek',
        'slug' => 'kakaos-csiga',
    ]);

    $response = $this->actingAs($user)->post('/admin/products', [
        'category_id' => $category->id,
        'name' => 'Kakaos csiga',
        'slug' => 'Kakaos csiga!!!',
        'short_description' => null,
        'description' => null,
        'price' => 1450,
        'is_active' => true,
        'is_featured' => false,
        'stock_status' => 'in_stock',
        'image_path' => null,
        'sort_order' => 3,
    ]);

    $response->assertRedirect('/admin/products');

    $this->assertDatabaseHas('products', [
        'name' => 'Kakaos csiga',
        'slug' => 'kakaos-csiga-2',
        'category_id' => $category->id,
    ]);
});

it('product create invalid adatokkal hibazik', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/products', [
        'category_id' => null,
        'name' => '',
        'price' => -50,
        'is_active' => true,
        'is_featured' => false,
        'stock_status' => 'unknown',
        'sort_order' => -2,
    ]);

    $response->assertSessionHasErrors(['category_id', 'name', 'price', 'stock_status', 'sort_order']);
});

it('product update mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Regi nev',
        'slug' => 'regi-nev',
    ]);

    $response = $this->actingAs($user)->put("/admin/products/{$product->id}", [
        'category_id' => $category->id,
        'name' => 'Uj nev',
        'short_description' => 'Rovid',
        'description' => 'Leiras',
        'price' => 1990,
        'is_active' => true,
        'is_featured' => true,
        'stock_status' => 'preorder',
        'image_path' => null,
        'sort_order' => 4,
    ]);

    $response->assertRedirect('/admin/products');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Uj nev',
        'slug' => 'uj-nev',
        'stock_status' => 'preorder',
    ]);
});

it('product delete soft delete-ol', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $response = $this->actingAs($user)->delete("/admin/products/{$product->id}");

    $response->assertRedirect('/admin/products');
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

it('product category filter mukodik', function (): void {
    $user = User::factory()->create();
    $bread = Category::factory()->create(['name' => 'Kenyerek', 'is_active' => true]);
    $sweet = Category::factory()->create(['name' => 'Édes Pékáru', 'is_active' => true]);

    Product::factory()->create(['name' => 'Focaccia', 'slug' => 'focaccia', 'category_id' => $bread->id]);
    Product::factory()->create(['name' => 'Kakaos csiga', 'slug' => 'kakaos-csiga', 'category_id' => $sweet->id]);

    $response = $this->actingAs($user)->get("/admin/products?category_id={$sweet->id}");

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Products/Index')
        ->has('products.data', 1)
        ->where('products.data.0.name', 'Kakaos csiga'));
});

it('product search mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Product::factory()->create(['name' => 'Magvas vekni', 'slug' => 'magvas-vekni', 'category_id' => $category->id]);
    Product::factory()->create(['name' => 'Kakaos csiga', 'slug' => 'kakaos-csiga', 'category_id' => $category->id]);

    $response = $this->actingAs($user)->get('/admin/products?search=magvas');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Products/Index')
        ->has('products.data', 1)
        ->where('products.data.0.name', 'Magvas vekni'));
});

it('product pagination mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Product::factory()->count(13)->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)->get('/admin/products?per_page=10&page=2');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Products/Index')
        ->where('products.current_page', 2)
        ->has('products.data', 3));
});

it('products index a szukseges listazo mezoket visszaadja', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Vajas croissant',
        'slug' => 'vajas-croissant',
        'price' => 890,
        'sort_order' => 9,
    ]);

    $response = $this->actingAs($user)->get('/admin/products');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Products/Index')
        ->where('products.data.0.id', $product->id)
        ->where('products.data.0.name', 'Vajas croissant')
        ->where('products.data.0.slug', 'vajas-croissant')
        ->where('products.data.0.price', 890)
        ->where('products.data.0.sort_order', 9));
});
