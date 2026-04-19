<?php

use App\Models\Category;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('categories index page auth nelkul tiltott', function (): void {
    $response = $this->get('/admin/categories');

    $response->assertRedirect('/login');
});

it('categories index page auth-val elerheto', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/categories');

    $response->assertOk();
});

it('category create valid adatokkal mukodik', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/categories', [
        'name' => 'Kenyerek',
        'slug' => 'kenyerek',
        'description' => 'Alap kovaszos termekek',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response->assertRedirect('/admin/categories');

    $this->assertDatabaseHas('categories', [
        'name' => 'Kenyerek',
        'slug' => 'kenyerek',
        'is_active' => true,
    ]);
});

it('category create invalid adatokkal hibazik', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/categories', [
        'name' => '',
        'slug' => 'invalid slug',
        'is_active' => true,
        'sort_order' => -1,
    ]);

    $response->assertSessionHasErrors(['name', 'slug', 'sort_order']);
});

it('category update mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'name' => 'Edes pekaru',
        'slug' => 'edes-pekaru',
    ]);

    $response = $this->actingAs($user)->put("/admin/categories/{$category->id}", [
        'name' => 'Edes peksutemenyek',
        'slug' => 'edes-peksutemenyek',
        'description' => 'Friss csigak es tekercsek',
        'is_active' => true,
        'sort_order' => 2,
    ]);

    $response->assertRedirect('/admin/categories');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Edes peksutemenyek',
        'slug' => 'edes-peksutemenyek',
    ]);
});

it('category delete soft delete-ol', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->delete("/admin/categories/{$category->id}");

    $response->assertRedirect('/admin/categories');
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

it('category kereses mukodik', function (): void {
    $user = User::factory()->create();

    Category::factory()->create(['name' => 'Kenyerek', 'slug' => 'kenyerek']);
    Category::factory()->create(['name' => 'Pizza', 'slug' => 'pizza']);

    $response = $this->actingAs($user)->get('/admin/categories?search=keny');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Categories/Index')
        ->has('categories.data', 1)
        ->where('categories.data.0.name', 'Kenyerek'));
});

it('category lapozas mukodik', function (): void {
    $user = User::factory()->create();

    Category::factory()->count(12)->create();

    $response = $this->actingAs($user)->get('/admin/categories?per_page=10&page=2');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Categories/Index')
        ->where('categories.current_page', 2)
        ->has('categories.data', 2));
});
