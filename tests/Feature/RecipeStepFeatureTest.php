<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\RecipeStep;
use App\Models\User;

it('recipe step create mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)
        ->from('/admin/recipes')
        ->post("/admin/products/{$product->id}/recipe-steps", [
            'title' => 'Dagasztas',
            'step_type' => 'mixing',
            'description' => 'Lassu fokozat majd kozepes.',
            'work_instruction' => 'Gyursd simara a tesztat.',
            'completion_criteria' => 'Selymes, rugalmas allag.',
            'attention_points' => 'Ne melegedjen 26C fole.',
            'required_tools' => 'Spiral dagasztogep',
            'expected_result' => 'Egyenletesen kidolgozott teszta',
            'duration_minutes' => 18,
            'wait_minutes' => 0,
            'temperature_celsius' => 24.5,
            'sort_order' => 2,
            'is_active' => true,
        ]);

    $response->assertRedirect('/admin/recipes');
    $this->assertDatabaseHas('recipe_steps', [
        'product_id' => $product->id,
        'title' => 'Dagasztas',
        'step_type' => 'mixing',
        'work_instruction' => 'Gyursd simara a tesztat.',
        'sort_order' => 2,
    ]);
});

it('recipe step update mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $step = RecipeStep::factory()->create([
        'product_id' => $product->id,
        'title' => 'Pihentetes',
        'duration_minutes' => 0,
        'wait_minutes' => 30,
    ]);

    $response = $this->actingAs($user)
        ->from('/admin/recipes')
        ->put("/admin/products/{$product->id}/recipe-steps/{$step->id}", [
            'title' => 'Pihentetes frissitve',
            'step_type' => 'resting',
            'description' => null,
            'work_instruction' => 'Takard le es pihentesd.',
            'completion_criteria' => 'Lathato gazkepzodes.',
            'attention_points' => 'Huzatmentes kornyezet.',
            'required_tools' => 'Erteto doboz',
            'expected_result' => 'Elerte a kivant eresi szintet',
            'duration_minutes' => 5,
            'wait_minutes' => 25,
            'temperature_celsius' => null,
            'sort_order' => 3,
            'is_active' => true,
        ]);

    $response->assertRedirect('/admin/recipes');
    $this->assertDatabaseHas('recipe_steps', [
        'id' => $step->id,
        'title' => 'Pihentetes frissitve',
        'work_instruction' => 'Takard le es pihentesd.',
        'sort_order' => 3,
    ]);
});

it('recipe step delete mukodik', function (): void {
    $user = User::factory()->create();
    $step = RecipeStep::factory()->create();

    $response = $this->actingAs($user)
        ->from('/admin/recipes')
        ->delete("/admin/products/{$step->product_id}/recipe-steps/{$step->id}");

    $response->assertRedirect('/admin/recipes');
    $this->assertDatabaseMissing('recipe_steps', [
        'id' => $step->id,
    ]);
});

it('recipe step validacio elutasitja ha nincs aktiv vagy varakozasi ido', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)
        ->post("/admin/products/{$product->id}/recipe-steps", [
            'title' => 'Ido nelkuli lepes',
            'step_type' => 'mixing',
            'description' => null,
            'duration_minutes' => 0,
            'wait_minutes' => 0,
            'temperature_celsius' => null,
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors(['duration_minutes', 'wait_minutes']);
});
