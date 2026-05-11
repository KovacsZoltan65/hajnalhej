<?php

use App\Data\Ingredients\IngredientIndexData;
use App\Data\Ingredients\IngredientListItemData;
use App\Data\Ingredients\IngredientStoreData;
use App\Data\Ingredients\IngredientUpdateData;
use App\Models\Ingredient;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('ingredients index guest nelkul tiltott', function (): void {
    $response = $this->get('/admin/ingredients');

    $response->assertRedirect('/login');
});

it('ingredients index auth-val elerheto', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/ingredients');

    $response->assertOk();
});

it('ingredient create valid adatokkal mukodik', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/ingredients', [
        'name' => 'Buzaliszt finom',
        'slug' => 'buzaliszt-finom',
        'sku' => 'ING-BF-01',
        'unit' => 'kg',
        'current_stock' => 30,
        'minimum_stock' => 12,
        'is_active' => true,
        'notes' => 'Teszt alapanyag',
    ]);

    $response->assertRedirect('/admin/ingredients');

    $this->assertDatabaseHas('ingredients', [
        'name' => 'Buzaliszt finom',
        'slug' => 'buzaliszt-finom',
        'sku' => 'ING-BF-01',
    ]);
});

it('ingredient create invalid adatokkal hibazik', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/ingredients', [
        'name' => '',
        'slug' => 'invalid slug',
        'unit' => 'meter',
        'current_stock' => -1,
        'minimum_stock' => -5,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['name', 'slug', 'unit', 'current_stock', 'minimum_stock']);
});

it('ingredient update mukodik', function (): void {
    $user = User::factory()->create();
    $ingredient = Ingredient::factory()->create([
        'name' => 'Regi alapanyag',
        'slug' => 'regi-alapanyag',
    ]);

    $response = $this->actingAs($user)->put("/admin/ingredients/{$ingredient->id}", [
        'name' => 'Uj alapanyag',
        'slug' => 'uj-alapanyag',
        'sku' => 'ING-UJ-001',
        'unit' => 'g',
        'current_stock' => 800,
        'minimum_stock' => 500,
        'is_active' => true,
        'notes' => null,
    ]);

    $response->assertRedirect('/admin/ingredients');

    $this->assertDatabaseHas('ingredients', [
        'id' => $ingredient->id,
        'name' => 'Uj alapanyag',
        'slug' => 'uj-alapanyag',
        'unit' => 'g',
    ]);
});

it('ingredient delete soft delete-ol', function (): void {
    $user = User::factory()->create();
    $ingredient = Ingredient::factory()->create();

    $response = $this->actingAs($user)->delete("/admin/ingredients/{$ingredient->id}");

    $response->assertRedirect('/admin/ingredients');
    $this->assertSoftDeleted('ingredients', ['id' => $ingredient->id]);
});

it('ingredient kereses mukodik', function (): void {
    $user = User::factory()->create();

    Ingredient::factory()->create(['name' => 'Buzaliszt premium', 'slug' => 'buzaliszt-premium']);
    Ingredient::factory()->create(['name' => 'Vaj osztrak', 'slug' => 'vaj-osztrak']);

    $response = $this->actingAs($user)->get('/admin/ingredients?search=buzaliszt');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Ingredients/Index')
        ->has('ingredients.data', 1)
        ->where('ingredients.data.0.name', 'Buzaliszt premium'));
});

it('ingredient index payload tartalmazza a becsult egysegkoltseget', function (): void {
    $user = User::factory()->create();

    Ingredient::factory()->create([
        'name' => 'Mandulaliszt',
        'slug' => 'mandulaliszt',
        'estimated_unit_cost' => 1850.5,
    ]);

    $response = $this->actingAs($user)->get('/admin/ingredients?search=mandulaliszt');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Ingredients/Index')
        ->where('ingredients.data.0.estimated_unit_cost', 1850.5));
});

it('ingredient pagination mukodik', function (): void {
    $user = User::factory()->create();

    Ingredient::factory()->count(14)->create();

    $response = $this->actingAs($user)->get('/admin/ingredients?per_page=10&page=2');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Ingredients/Index')
        ->where('ingredients.current_page', 2)
        ->has('ingredients.data', 4));
});

it('ingredient low stock logika ellenorizheto', function (): void {
    $user = User::factory()->create();

    Ingredient::factory()->create([
        'name' => 'Eleszto friss',
        'slug' => 'eleszto-friss',
        'current_stock' => 1.5,
        'minimum_stock' => 2,
    ]);

    $response = $this->actingAs($user)->get('/admin/ingredients?search=eleszto-friss');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Ingredients/Index')
        ->where('ingredients.data.0.is_low_stock', true));
});

it('ingredient inline update mukodik', function (): void {
    $user = User::factory()->create();
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 3,
        'minimum_stock' => 5,
    ]);

    $response = $this->actingAs($user)->patch("/admin/ingredients/{$ingredient->id}/inline", [
        'field' => 'current_stock',
        'value' => 12.75,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('ingredients', [
        'id' => $ingredient->id,
        'current_stock' => '12.750',
    ]);
});

it('ingredient store data keeps typed normalized payload', function (): void {
    $data = IngredientStoreData::from([
        'name' => '  BL80 liszt  ',
        'slug' => '',
        'sku' => '  LISZT-80  ',
        'unit' => 'kg',
        'estimated_unit_cost' => 185.5,
        'current_stock' => 12,
        'minimum_stock' => '5.25',
        'is_active' => true,
        'notes' => 'Teszt',
    ]);

    expect($data->toPayload())->toMatchArray([
        'name' => 'BL80 liszt',
        'slug' => '',
        'sku' => 'LISZT-80',
        'unit' => 'kg',
        'estimated_unit_cost' => '185.5000',
        'current_stock' => '12.000',
        'minimum_stock' => '5.250',
        'is_active' => true,
        'notes' => 'Teszt',
    ]);
});

it('ingredient update data handles optional fields', function (): void {
    $data = IngredientUpdateData::from([
        'name' => 'Vaj',
        'slug' => null,
        'sku' => '',
        'unit' => 'kg',
        'estimated_unit_cost' => null,
        'current_stock' => null,
        'minimum_stock' => null,
        'is_active' => false,
        'notes' => null,
    ]);

    expect($data->toPayload())->toMatchArray([
        'name' => 'Vaj',
        'slug' => '',
        'sku' => null,
        'estimated_unit_cost' => '0.0000',
        'current_stock' => '0.000',
        'minimum_stock' => '0.000',
        'is_active' => false,
        'notes' => null,
    ]);
});

it('ingredient list item data exposes stock fields', function (): void {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Friss eleszto',
        'slug' => 'friss-eleszto',
        'unit' => 'g',
        'estimated_unit_cost' => 12.3456,
        'current_stock' => 2,
        'minimum_stock' => 3,
        'is_active' => true,
    ]);

    $data = IngredientListItemData::from($ingredient)->toArray();

    expect($data)->toMatchArray([
        'name' => 'Friss eleszto',
        'unit' => 'g',
        'estimated_unit_cost' => 12.3456,
        'current_stock' => 2.0,
        'minimum_stock' => 3.0,
        'is_low_stock' => true,
        'is_active' => true,
    ]);
});

it('ingredient index data exposes stable filter payload', function (): void {
    $filters = IngredientIndexData::from([
        'search' => '  liszt  ',
        'is_active' => '0',
        'unit' => 'kg',
        'sort_field' => 'current_stock',
        'sort_direction' => 'desc',
        'per_page' => 20,
    ]);

    expect($filters->toFrontendFilters())->toBe([
        'search' => 'liszt',
        'is_active' => '0',
        'unit' => 'kg',
        'sort_field' => 'current_stock',
        'sort_direction' => 'desc',
        'per_page' => 20,
    ]);
});
