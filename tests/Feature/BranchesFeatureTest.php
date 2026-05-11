<?php

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('jogosulatlan user nem eri el a branches indexet', function (): void {
    $user = User::factory()->customer()->create();

    $this->actingAs($user)
        ->get('/admin/branches')
        ->assertForbidden();
});

it('jogosult user listazza a brancheket', function (): void {
    $user = User::factory()->admin()->create();
    Branch::factory()->create(['name' => 'Hajnalhéj Teszt Üzlet', 'code' => 'TEST-SHOP']);

    $this->actingAs($user)
        ->get('/admin/branches')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Branches/Index')
            ->has('branches.data', 1)
            ->where('branches.data.0.code', 'TEST-SHOP')
            ->has('options.types'));
});

it('branch create valid adatokkal mukodik', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post('/admin/branches', [
            'name' => 'Hajnalhéj Új Pékség',
            'code' => 'bakery-new',
            'type' => 'bakery',
            'email' => 'uj@hajnalhej.hu',
            'phone' => '+36 30 111 1111',
            'address' => 'Budapest, Kovász utca 10.',
            'active' => true,
            'meta' => ['note' => 'nyitás előtt'],
        ])
        ->assertRedirect('/admin/branches');

    $this->assertDatabaseHas('branches', [
        'name' => 'Hajnalhéj Új Pékség',
        'code' => 'BAKERY-NEW',
        'type' => 'bakery',
        'active' => true,
    ]);
});

it('branch create invalid type hibazik', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post('/admin/branches', [
            'name' => 'Hibás telephely',
            'code' => 'BAD-01',
            'type' => 'shop_and_bakery',
            'active' => true,
        ])
        ->assertSessionHasErrors('type');
});

it('branch update mukodik', function (): void {
    $user = User::factory()->admin()->create();
    $branch = Branch::factory()->create(['code' => 'SHOP-OLD', 'type' => 'shop']);

    $this->actingAs($user)
        ->put("/admin/branches/{$branch->id}", [
            'name' => 'Frissített üzlet',
            'code' => 'SHOP-NEW',
            'type' => 'pickup_point',
            'email' => 'frissitett@hajnalhej.hu',
            'phone' => '+36 30 222 2222',
            'address' => 'Budapest, Reggel tér 3.',
            'active' => false,
            'meta' => null,
        ])
        ->assertRedirect('/admin/branches');

    $this->assertDatabaseHas('branches', [
        'id' => $branch->id,
        'name' => 'Frissített üzlet',
        'code' => 'SHOP-NEW',
        'type' => 'pickup_point',
        'active' => false,
    ]);
});

it('branch code unique validacio mukodik', function (): void {
    $user = User::factory()->admin()->create();
    Branch::factory()->create(['code' => 'UNIQUE-01']);

    $this->actingAs($user)
        ->post('/admin/branches', [
            'name' => 'Duplikált üzlet',
            'code' => 'UNIQUE-01',
            'type' => 'shop',
            'active' => true,
        ])
        ->assertSessionHasErrors('code');
});

it('branch delete mukodik', function (): void {
    $user = User::factory()->admin()->create();
    $branch = Branch::factory()->create();

    $this->actingAs($user)
        ->delete("/admin/branches/{$branch->id}")
        ->assertRedirect('/admin/branches');

    $this->assertSoftDeleted('branches', ['id' => $branch->id]);
});
