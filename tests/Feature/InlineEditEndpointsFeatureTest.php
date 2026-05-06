<?php

use App\Models\Ingredient;
use App\Models\User;

it('ingredient inline endpoint csak engedelyezett mezot fogad', function (): void {
    $user = User::factory()->create();
    $ingredient = Ingredient::factory()->create();

    $response = $this->actingAs($user)->patch("/admin/ingredients/{$ingredient->id}/inline", [
        'field' => 'name',
        'value' => 'Liszt',
    ]);

    $response->assertSessionHasErrors(['field']);
});

it('ingredient inline endpoint jogosultsag nelkul 403', function (): void {
    $user = User::factory()->customer()->create();
    $ingredient = Ingredient::factory()->create();

    $response = $this->actingAs($user)->patch("/admin/ingredients/{$ingredient->id}/inline", [
        'field' => 'current_stock',
        'value' => 10,
    ]);

    $response->assertForbidden();
});

it('ingredient inline endpoint validacio hibanal 422 ajax keresnel', function (): void {
    $user = User::factory()->create();
    $ingredient = Ingredient::factory()->create();

    $response = $this->actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->withHeader('Accept', 'application/json')
        ->patch("/admin/ingredients/{$ingredient->id}/inline", [
            'field' => 'unit',
            'value' => 'doboz',
        ]);

    $response->assertStatus(422);
});

it('ingredient inline endpoint frissiti a rekordot', function (): void {
    $user = User::factory()->create();
    $ingredient = Ingredient::factory()->create(['current_stock' => 1]);

    $response = $this->actingAs($user)->patch("/admin/ingredients/{$ingredient->id}/inline", [
        'field' => 'current_stock',
        'value' => 12.5,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('ingredients', [
        'id' => $ingredient->id,
        'current_stock' => '12.500',
    ]);
});
