<?php

use App\Data\IngredientSupplierTerms\IngredientSupplierTermIndexData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermListItemData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermStoreData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermUpdateData;
use App\Models\Ingredient;
use App\Models\IngredientSupplierTerm;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function supplierTermPayload(Ingredient $ingredient, Supplier $supplier, array $overrides = []): array
{
    return array_merge([
        'ingredient_id' => $ingredient->id,
        'supplier_id' => $supplier->id,
        'lead_time_days' => 3,
        'minimum_order_quantity' => 25,
        'pack_size' => 5,
        'unit_cost_override' => 1200,
        'preferred' => false,
        'active' => true,
        'meta' => '{"note":"test"}',
    ], $overrides);
}

it('lists ingredient supplier terms for admins', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create(['name' => 'BL80 liszt', 'unit' => 'kg']);
    $supplier = Supplier::factory()->create(['name' => 'Malom Teszt']);
    IngredientSupplierTerm::query()->create(supplierTermPayload($ingredient, $supplier, ['meta' => null]));

    $this->actingAs($admin)
        ->get('/admin/ingredient-supplier-terms')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/IngredientSupplierTerms/Index')
            ->has('terms.data', 1)
            ->where('terms.data.0.ingredient_name', 'BL80 liszt')
            ->where('terms.data.0.supplier_name', 'Malom Teszt'));
});

it('creates ingredient supplier terms', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create();
    $supplier = Supplier::factory()->create();

    $this->actingAs($admin)
        ->post('/admin/ingredient-supplier-terms', supplierTermPayload($ingredient, $supplier, ['preferred' => true]))
        ->assertRedirect();

    $this->assertDatabaseHas('ingredient_supplier_terms', [
        'ingredient_id' => $ingredient->id,
        'supplier_id' => $supplier->id,
        'preferred' => true,
        'active' => true,
    ]);
});

it('updates ingredient supplier terms', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create();
    $supplier = Supplier::factory()->create();
    $term = IngredientSupplierTerm::query()->create(supplierTermPayload($ingredient, $supplier, ['meta' => null]));

    $this->actingAs($admin)
        ->put("/admin/ingredient-supplier-terms/{$term->id}", supplierTermPayload($ingredient, $supplier, [
            'lead_time_days' => 9,
            'pack_size' => 10,
            'meta' => '{"updated":true}',
        ]))
        ->assertRedirect();

    $this->assertDatabaseHas('ingredient_supplier_terms', [
        'id' => $term->id,
        'lead_time_days' => 9,
        'pack_size' => 10,
    ]);
});

it('soft deletes ingredient supplier terms', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create();
    $supplier = Supplier::factory()->create();
    $term = IngredientSupplierTerm::query()->create(supplierTermPayload($ingredient, $supplier, ['meta' => null]));

    $this->actingAs($admin)
        ->delete("/admin/ingredient-supplier-terms/{$term->id}")
        ->assertRedirect();

    expect(IngredientSupplierTerm::query()->find($term->id))->toBeNull();
    expect(IngredientSupplierTerm::withTrashed()->find($term->id)?->deleted_at)->not->toBeNull();
});

it('rejects duplicate ingredient and supplier pair', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create();
    $supplier = Supplier::factory()->create();
    IngredientSupplierTerm::query()->create(supplierTermPayload($ingredient, $supplier, ['meta' => null]));

    $this->actingAs($admin)
        ->from('/admin/ingredient-supplier-terms')
        ->post('/admin/ingredient-supplier-terms', supplierTermPayload($ingredient, $supplier))
        ->assertRedirect('/admin/ingredient-supplier-terms')
        ->assertSessionHasErrors('supplier_id');
});

it('keeps only one active preferred supplier per ingredient', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create();
    $firstSupplier = Supplier::factory()->create();
    $secondSupplier = Supplier::factory()->create();
    $firstTerm = IngredientSupplierTerm::query()->create(supplierTermPayload($ingredient, $firstSupplier, [
        'preferred' => true,
        'meta' => null,
    ]));

    $this->actingAs($admin)
        ->post('/admin/ingredient-supplier-terms', supplierTermPayload($ingredient, $secondSupplier, ['preferred' => true]))
        ->assertRedirect();

    expect($firstTerm->fresh()->preferred)->toBeFalse();
    expect(IngredientSupplierTerm::query()->where('ingredient_id', $ingredient->id)->where('active', true)->where('preferred', true)->count())->toBe(1);
});

it('rejects inactive preferred terms', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create();
    $supplier = Supplier::factory()->create();

    $this->actingAs($admin)
        ->from('/admin/ingredient-supplier-terms')
        ->post('/admin/ingredient-supplier-terms', supplierTermPayload($ingredient, $supplier, [
            'preferred' => true,
            'active' => false,
        ]))
        ->assertRedirect('/admin/ingredient-supplier-terms')
        ->assertSessionHasErrors('preferred');
});

it('enforces policy access for customers', function (): void {
    $customer = User::factory()->customer()->create();
    $ingredient = Ingredient::factory()->create();
    $supplier = Supplier::factory()->create();

    $this->actingAs($customer)
        ->get('/admin/ingredient-supplier-terms')
        ->assertForbidden();

    $this->actingAs($customer)
        ->post('/admin/ingredient-supplier-terms', supplierTermPayload($ingredient, $supplier))
        ->assertForbidden();
});

it('ingredient supplier term store data normalizes validated payload', function (): void {
    $data = IngredientSupplierTermStoreData::from([
        'ingredient_id' => 5,
        'supplier_id' => 9,
        'lead_time_days' => 4,
        'minimum_order_quantity' => '25.500',
        'pack_size' => '',
        'unit_cost_override' => 1200,
        'preferred' => true,
        'active' => true,
        'meta' => '{"note":"teszt"}',
    ]);

    expect($data->toPayload())->toMatchArray([
        'ingredient_id' => 5,
        'supplier_id' => 9,
        'lead_time_days' => 4,
        'minimum_order_quantity' => '25.500',
        'pack_size' => null,
        'unit_cost_override' => '1200',
        'preferred' => true,
        'active' => true,
        'meta' => ['note' => 'teszt'],
    ]);
});

it('ingredient supplier term update data handles optional fields', function (): void {
    $data = IngredientSupplierTermUpdateData::from([
        'ingredient_id' => 5,
        'supplier_id' => 9,
        'lead_time_days' => null,
        'minimum_order_quantity' => null,
        'pack_size' => '',
        'unit_cost_override' => '',
        'preferred' => true,
        'active' => false,
        'meta' => '',
    ]);

    expect($data->toPayload())->toMatchArray([
        'lead_time_days' => null,
        'minimum_order_quantity' => null,
        'pack_size' => null,
        'unit_cost_override' => null,
        'preferred' => false,
        'active' => false,
        'meta' => null,
    ]);
});

it('ingredient supplier term index data exposes stable frontend filters', function (): void {
    $filters = IngredientSupplierTermIndexData::from([
        'search' => '  liszt  ',
        'active' => '1',
        'sort_field' => 'supplier',
        'sort_direction' => 'desc',
        'per_page' => 20,
    ]);

    expect($filters->toFrontendFilters())->toBe([
        'search' => 'liszt',
        'active' => '1',
        'sort_field' => 'supplier',
        'sort_direction' => 'desc',
        'per_page' => 20,
    ]);
});

it('ingredient supplier term list item data maps relation fields', function (): void {
    $ingredient = Ingredient::factory()->create(['name' => 'BL80 liszt', 'unit' => 'kg']);
    $supplier = Supplier::factory()->create(['name' => 'Malom Kft.']);
    $term = IngredientSupplierTerm::query()
        ->create(supplierTermPayload($ingredient, $supplier, ['meta' => ['quality' => 'premium']]))
        ->load(['ingredient', 'supplier']);

    $data = IngredientSupplierTermListItemData::from($term)->toArray();

    expect($data)
        ->toMatchArray([
            'ingredient_name' => 'BL80 liszt',
            'ingredient_unit' => 'kg',
            'supplier_name' => 'Malom Kft.',
            'meta' => ['quality' => 'premium'],
        ]);
});
