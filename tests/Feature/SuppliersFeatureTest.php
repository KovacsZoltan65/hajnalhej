<?php

use App\Data\Suppliers\SupplierIndexData;
use App\Data\Suppliers\SupplierListItemData;
use App\Data\Suppliers\SupplierStoreData;
use App\Data\Suppliers\SupplierUpdateData;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function supplierPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Malom Teszt Kft.',
        'email' => 'malom@example.test',
        'phone' => '+36 1 234 5678',
        'tax_number' => '12345678-1-42',
        'lead_time_days' => 4,
        'notes' => 'Teszt beszállító',
    ], $overrides);
}

it('lists suppliers for admins', function (): void {
    $admin = User::factory()->admin()->create();
    Supplier::factory()->create(['name' => 'Malom Teszt Kft.', 'email' => 'malom@example.test']);

    $this->actingAs($admin)
        ->get('/admin/suppliers?search=malom')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Suppliers/Index')
            ->has('suppliers.data', 1)
            ->where('suppliers.data.0.name', 'Malom Teszt Kft.')
            ->where('suppliers.data.0.email', 'malom@example.test'));
});

it('creates suppliers', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/suppliers', supplierPayload())
        ->assertRedirect();

    $this->assertDatabaseHas('suppliers', [
        'name' => 'Malom Teszt Kft.',
        'email' => 'malom@example.test',
        'lead_time_days' => 4,
    ]);
});

it('updates suppliers', function (): void {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create(['name' => 'Regi Malom']);

    $this->actingAs($admin)
        ->put("/admin/suppliers/{$supplier->id}", supplierPayload([
            'name' => 'Uj Malom',
            'email' => '',
            'lead_time_days' => 9,
        ]))
        ->assertRedirect();

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'Uj Malom',
        'email' => null,
        'lead_time_days' => 9,
    ]);
});

it('enforces supplier policy access for customers', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/suppliers')
        ->assertForbidden();

    $this->actingAs($customer)
        ->post('/admin/suppliers', supplierPayload())
        ->assertForbidden();
});

it('supplier store data normalizes contact fields and lead time', function (): void {
    $data = SupplierStoreData::from([
        'name' => '  Malom Teszt Kft.  ',
        'email' => '  malom@example.test  ',
        'phone' => '',
        'tax_number' => ' 12345678-1-42 ',
        'lead_time_days' => '7',
        'notes' => '',
    ]);

    expect($data->toPayload())->toBe([
        'name' => 'Malom Teszt Kft.',
        'email' => 'malom@example.test',
        'phone' => null,
        'tax_number' => '12345678-1-42',
        'lead_time_days' => 7,
        'notes' => null,
    ]);
});

it('supplier update data handles optional fields', function (): void {
    $data = SupplierUpdateData::from([
        'name' => 'Malom Teszt Kft.',
        'email' => null,
        'phone' => null,
        'tax_number' => null,
        'lead_time_days' => null,
        'notes' => null,
    ]);

    expect($data->toPayload())->toMatchArray([
        'email' => null,
        'phone' => null,
        'tax_number' => null,
        'lead_time_days' => null,
        'notes' => null,
    ]);
});

it('supplier index data exposes stable filter payload', function (): void {
    $filters = SupplierIndexData::from([
        'search' => '  malom  ',
        'sort_field' => 'created_at',
        'sort_direction' => 'desc',
        'per_page' => 30,
    ]);

    expect($filters->toFrontendFilters())->toBe([
        'search' => 'malom',
        'sort_field' => 'created_at',
        'sort_direction' => 'desc',
        'per_page' => 30,
    ]);
});

it('supplier list item data maps list fields', function (): void {
    $supplier = Supplier::factory()->create([
        'name' => 'Malom Kft.',
        'email' => 'kapcsolat@malom.test',
        'phone' => '+36 30 123 4567',
        'tax_number' => '12345678-1-42',
        'lead_time_days' => 5,
        'active' => false,
    ]);
    $supplier->setAttribute('purchases_count', 3);

    $data = SupplierListItemData::from($supplier)->toArray();

    expect($data)->toMatchArray([
        'name' => 'Malom Kft.',
        'email' => 'kapcsolat@malom.test',
        'phone' => '+36 30 123 4567',
        'tax_number' => '12345678-1-42',
        'lead_time_days' => 5,
        'active' => false,
        'purchases_count' => 3,
    ]);
});
