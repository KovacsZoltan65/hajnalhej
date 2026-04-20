<?php

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('recipes index guest nem fer hozza', function (): void {
    $response = $this->get('/admin/recipes');

    $response->assertRedirect('/login');
});

it('recipes index auth user hozzafer', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/recipes');

    $response->assertOk();
});

it('recipes index listazza a productokat receptnezetben', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true, 'name' => 'Kenyerek']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Kovaszos teszt kenyer',
        'slug' => 'kovaszos-teszt-kenyer',
    ]);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);

    ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
    ]);

    $response = $this->actingAs($user)->get('/admin/recipes');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->has('recipes.data', 1)
        ->where('recipes.data.0.name', 'Kovaszos teszt kenyer')
        ->where('recipes.data.0.recipe_items_count', 1));
});

it('recipes search mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Product::factory()->create(['category_id' => $category->id, 'name' => 'Magvas vekni', 'slug' => 'magvas-vekni']);
    Product::factory()->create(['category_id' => $category->id, 'name' => 'Kakaos csiga', 'slug' => 'kakaos-csiga']);

    $response = $this->actingAs($user)->get('/admin/recipes?search=magvas');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->has('recipes.data', 1)
        ->where('recipes.data.0.slug', 'magvas-vekni'));
});

it('recipes product_id szures egy adott termekre fokuszal', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $target = Product::factory()->create(['category_id' => $category->id, 'name' => 'Target product']);
    Product::factory()->create(['category_id' => $category->id, 'name' => 'Other product']);

    $response = $this->actingAs($user)->get("/admin/recipes?product_id={$target->id}");

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->has('recipes.data', 1)
        ->where('recipes.data.0.id', $target->id)
        ->where('filters.product_id', (string) $target->id));
});

it('recipes kategoria szures mukodik', function (): void {
    $user = User::factory()->create();
    $bread = Category::factory()->create(['name' => 'Kenyerek', 'is_active' => true]);
    $sweet = Category::factory()->create(['name' => 'Edes', 'is_active' => true]);

    Product::factory()->create(['category_id' => $bread->id, 'name' => 'Focaccia', 'slug' => 'focaccia']);
    Product::factory()->create(['category_id' => $sweet->id, 'name' => 'Csiga', 'slug' => 'csiga']);

    $response = $this->actingAs($user)->get("/admin/recipes?category_id={$sweet->id}");

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->has('recipes.data', 1)
        ->where('recipes.data.0.name', 'Csiga'));
});

it('recipes with recipe es without recipe szures mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $with = Product::factory()->create(['category_id' => $category->id, 'name' => 'Van recept']);
    $without = Product::factory()->create(['category_id' => $category->id, 'name' => 'Nincs recept']);

    $ingredient = Ingredient::factory()->create(['is_active' => true]);
    ProductIngredient::factory()->create(['product_id' => $with->id, 'ingredient_id' => $ingredient->id]);

    $withResponse = $this->actingAs($user)->get('/admin/recipes?recipe_presence=with_recipe');
    $withResponse->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->has('recipes.data', 1)
        ->where('recipes.data.0.name', 'Van recept'));

    $withoutResponse = $this->actingAs($user)->get('/admin/recipes?recipe_presence=without_recipe');
    $withoutResponse->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->has('recipes.data', 1)
        ->where('recipes.data.0.name', 'Nincs recept'));

    expect($without->id)->not->toBe($with->id);
});

it('recipes low stock indikacio lekerezheto', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Low stock teszt',
        'slug' => 'low-stock-teszt',
    ]);
    $ingredient = Ingredient::factory()->create([
        'is_active' => true,
        'current_stock' => 1,
        'minimum_stock' => 2,
    ]);

    ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
    ]);

    $response = $this->actingAs($user)->get('/admin/recipes?search=low-stock');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->where('recipes.data.0.low_stock_ingredients_count', 1));
});

it('recipe editor adatokat betolti a szerkesztohoz', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create(['is_active' => true, 'name' => 'Teszt liszt']);

    ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
    ]);

    $response = $this->actingAs($user)->get('/admin/recipes');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->has('ingredients', fn (Assert $ingredients) => $ingredients
            ->where('0.name', 'Teszt liszt')
            ->etc())
        ->has('stepTypes', 7)
        ->has('recipes.data.0.product_ingredients', 1));
});

it('recipe summary szamitas tartalmazza az idozitesi osszegeket', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Idozitett termek']);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);

    ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
    ]);

    RecipeStep::factory()->create([
        'product_id' => $product->id,
        'duration_minutes' => 25,
        'wait_minutes' => 40,
    ]);
    RecipeStep::factory()->create([
        'product_id' => $product->id,
        'duration_minutes' => 10,
        'wait_minutes' => 5,
    ]);

    $response = $this->actingAs($user)->get('/admin/recipes');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->where('recipes.data.0.recipe_summary.total_active_minutes', 35)
        ->where('recipes.data.0.recipe_summary.total_wait_minutes', 45)
        ->where('recipes.data.0.recipe_summary.total_recipe_minutes', 80));
});

it('recipe lepesek sort_order szerint rendezettek', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Rendezes teszt']);

    RecipeStep::factory()->create([
        'product_id' => $product->id,
        'title' => 'Masodik',
        'sort_order' => 2,
        'duration_minutes' => 10,
        'wait_minutes' => 0,
    ]);
    RecipeStep::factory()->create([
        'product_id' => $product->id,
        'title' => 'Elso',
        'sort_order' => 1,
        'duration_minutes' => 5,
        'wait_minutes' => 0,
    ]);

    $response = $this->actingAs($user)->get("/admin/recipes?product_id={$product->id}");

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Recipes/Index')
        ->where('recipes.data.0.recipe_steps.0.title', 'Elso')
        ->where('recipes.data.0.recipe_steps.1.title', 'Masodik'));
});

it('recipe editorbol ingredient hozzaadas mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)
        ->from('/admin/recipes')
        ->post("/admin/products/{$product->id}/ingredients", [
            'ingredient_id' => $ingredient->id,
            'quantity' => 0.45,
            'sort_order' => 1,
            'notes' => 'Recipes oldalrol',
        ]);

    $response->assertRedirect('/admin/recipes');
    $this->assertDatabaseHas('product_ingredients', [
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
    ]);
});

it('recipe editorbol update mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);
    $item = ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
        'quantity' => 0.5,
    ]);

    $response = $this->actingAs($user)
        ->from('/admin/recipes')
        ->put("/admin/products/{$product->id}/ingredients/{$item->id}", [
            'ingredient_id' => $ingredient->id,
            'quantity' => 0.95,
            'sort_order' => 3,
            'notes' => 'Frissitve',
        ]);

    $response->assertRedirect('/admin/recipes');
    $this->assertDatabaseHas('product_ingredients', [
        'id' => $item->id,
        'sort_order' => 3,
        'notes' => 'Frissitve',
    ]);
});

it('recipe editorbol delete mukodik', function (): void {
    $user = User::factory()->create();
    $item = ProductIngredient::factory()->create();

    $response = $this->actingAs($user)
        ->from('/admin/recipes')
        ->delete("/admin/products/{$item->product_id}/ingredients/{$item->id}");

    $response->assertRedirect('/admin/recipes');
    $this->assertDatabaseMissing('product_ingredients', ['id' => $item->id]);
});
