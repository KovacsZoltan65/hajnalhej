<?php

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\User;

it('product ingredient hozzaadas mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->post("/admin/products/{$product->id}/ingredients", [
        'ingredient_id' => $ingredient->id,
        'quantity' => 0.550,
        'sort_order' => 2,
        'notes' => 'Teszt recept',
    ]);

    $response->assertRedirect('/admin/products');

    $this->assertDatabaseHas('product_ingredients', [
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
        'sort_order' => 2,
    ]);
});

it('ugyanaz az ingredient nem adhato ketszer ugyanahhoz a producthoz', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);

    ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
    ]);

    $response = $this->actingAs($user)->post("/admin/products/{$product->id}/ingredients", [
        'ingredient_id' => $ingredient->id,
        'quantity' => 0.250,
        'sort_order' => 0,
    ]);

    $response->assertSessionHasErrors(['ingredient_id']);
});

it('csak aktiv ingredient valaszthato', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create(['is_active' => false]);

    $response = $this->actingAs($user)->post("/admin/products/{$product->id}/ingredients", [
        'ingredient_id' => $ingredient->id,
        'quantity' => 0.4,
        'sort_order' => 0,
    ]);

    $response->assertSessionHasErrors(['ingredient_id']);
});

it('quantity validacio mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->post("/admin/products/{$product->id}/ingredients", [
        'ingredient_id' => $ingredient->id,
        'quantity' => 0,
        'sort_order' => 0,
    ]);

    $response->assertSessionHasErrors(['quantity']);
});

it('recipe update mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);
    $recipeItem = ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
        'quantity' => 0.5,
    ]);

    $response = $this->actingAs($user)->put("/admin/products/{$product->id}/ingredients/{$recipeItem->id}", [
        'ingredient_id' => $ingredient->id,
        'quantity' => 0.750,
        'sort_order' => 4,
        'notes' => 'Uj recept arany',
    ]);

    $response->assertRedirect('/admin/products');

    $this->assertDatabaseHas('product_ingredients', [
        'id' => $recipeItem->id,
        'sort_order' => 4,
        'notes' => 'Uj recept arany',
    ]);
});

it('recipe kapcsolat torlese mukodik', function (): void {
    $user = User::factory()->create();
    $recipeItem = ProductIngredient::factory()->create();

    $response = $this->actingAs($user)->delete("/admin/products/{$recipeItem->product_id}/ingredients/{$recipeItem->id}");

    $response->assertRedirect('/admin/products');
    $this->assertDatabaseMissing('product_ingredients', ['id' => $recipeItem->id]);
});

it('nested route nem enged idegen productIngredient rekordot modositani', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $productA = Product::factory()->create(['category_id' => $category->id]);
    $productB = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create(['is_active' => true]);

    $recipeItem = ProductIngredient::factory()->create([
        'product_id' => $productA->id,
        'ingredient_id' => $ingredient->id,
    ]);

    $response = $this->actingAs($user)->put("/admin/products/{$productB->id}/ingredients/{$recipeItem->id}", [
        'ingredient_id' => $ingredient->id,
        'quantity' => 0.3,
        'sort_order' => 1,
    ]);

    $response->assertNotFound();
});
