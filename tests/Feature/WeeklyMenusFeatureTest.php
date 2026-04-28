<?php

use App\Data\WeeklyMenu\WeeklyMenuIndexData;
use App\Data\WeeklyMenu\WeeklyMenuListItemData;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

it('weekly menus guest nem fer hozza admin indexhez', function (): void {
    $response = $this->get('/admin/weekly-menus');

    $response->assertRedirect('/login');
});

it('weekly menus auth user hozzafer', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/weekly-menus');

    $response->assertOk();
});

it('weekly menu create mukodik valid adatokkal', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/weekly-menus', [
        'title' => 'Aktuális heti menü',
        'slug' => 'aktualis-heti-menu',
        'week_start' => '2026-04-20',
        'week_end' => '2026-04-26',
        'status' => 'draft',
        'public_note' => 'Nyitvatartas valtozhat.',
        'internal_note' => 'Belso megjegyzes',
        'is_featured' => true,
    ]);

    $response->assertRedirect('/admin/weekly-menus');

    $this->assertDatabaseHas('weekly_menus', [
        'title' => 'Aktuális heti menü',
        'slug' => 'aktualis-heti-menu',
        'status' => 'draft',
    ]);
});

it('weekly menu create data flow elfogadja az uj typed mezoket', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/weekly-menus', [
        'name' => 'Majusi het',
        'start_date' => '2026-05-04',
        'end_date' => '2026-05-10',
        'is_active' => false,
        'is_featured' => false,
    ]);

    $response->assertRedirect('/admin/weekly-menus');

    $this->assertDatabaseHas('weekly_menus', [
        'title' => 'Majusi het',
        'slug' => 'majusi-het',
        'week_start' => '2026-05-04 00:00:00',
        'week_end' => '2026-05-10 00:00:00',
        'status' => WeeklyMenu::STATUS_DRAFT,
    ]);
});

it('weekly menu invalid date range hibazik', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/weekly-menus', [
        'title' => 'Hibas menu',
        'week_start' => '2026-04-26',
        'week_end' => '2026-04-20',
        'is_featured' => false,
    ]);

    $response->assertSessionHasErrors(['week_end']);
});

it('weekly menu update mukodik', function (): void {
    $user = User::factory()->create();
    $menu = WeeklyMenu::factory()->create([
        'title' => 'Regi',
        'slug' => 'regi',
    ]);

    $response = $this->actingAs($user)->put("/admin/weekly-menus/{$menu->id}", [
        'title' => 'Uj menu',
        'slug' => 'uj-menu',
        'week_start' => '2026-04-20',
        'week_end' => '2026-04-26',
        'status' => 'draft',
        'public_note' => null,
        'internal_note' => null,
        'is_featured' => false,
    ]);

    $response->assertRedirect('/admin/weekly-menus');

    $this->assertDatabaseHas('weekly_menus', [
        'id' => $menu->id,
        'title' => 'Uj menu',
        'slug' => 'uj-menu',
    ]);
});

it('weekly menu update data flow tud aktiv menut kezelni', function (): void {
    $user = User::factory()->create();
    $oldActive = WeeklyMenu::factory()->create([
        'title' => 'Regi aktiv',
        'week_start' => '2026-05-04',
        'week_end' => '2026-05-10',
        'status' => WeeklyMenu::STATUS_PUBLISHED,
        'published_at' => Carbon::now(),
    ]);
    $menu = WeeklyMenu::factory()->create([
        'title' => 'Uj jelolt',
        'slug' => 'uj-jelolt',
        'status' => WeeklyMenu::STATUS_DRAFT,
    ]);

    $response = $this->actingAs($user)->put("/admin/weekly-menus/{$menu->id}", [
        'name' => 'Uj aktiv het',
        'start_date' => '2026-05-06',
        'end_date' => '2026-05-12',
        'is_active' => true,
        'is_featured' => false,
    ]);

    $response->assertRedirect('/admin/weekly-menus');

    $this->assertDatabaseHas('weekly_menus', [
        'id' => $menu->id,
        'title' => 'Uj aktiv het',
        'status' => WeeklyMenu::STATUS_PUBLISHED,
    ]);
    $this->assertDatabaseHas('weekly_menus', [
        'id' => $oldActive->id,
        'status' => WeeklyMenu::STATUS_ARCHIVED,
    ]);
});

it('weekly menu delete soft delete', function (): void {
    $user = User::factory()->create();
    $menu = WeeklyMenu::factory()->create();

    $response = $this->actingAs($user)->delete("/admin/weekly-menus/{$menu->id}");

    $response->assertRedirect('/admin/weekly-menus');
    $this->assertSoftDeleted('weekly_menus', ['id' => $menu->id]);
});

it('weekly menu search mukodik', function (): void {
    $user = User::factory()->create();
    WeeklyMenu::factory()->create(['title' => 'Kovaszos het', 'slug' => 'kovaszos-het']);
    WeeklyMenu::factory()->create(['title' => 'Teszt', 'slug' => 'teszt-menu']);

    $response = $this->actingAs($user)->get('/admin/weekly-menus?search=kovasz');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/WeeklyMenus/Index')
        ->has('weeklyMenus.data', 1)
        ->where('weeklyMenus.data.0.title', 'Kovaszos het'));
});

it('weekly menu status filter mukodik', function (): void {
    $user = User::factory()->create();
    WeeklyMenu::factory()->create(['status' => WeeklyMenu::STATUS_DRAFT]);
    WeeklyMenu::factory()->create(['status' => WeeklyMenu::STATUS_PUBLISHED]);

    $response = $this->actingAs($user)->get('/admin/weekly-menus?status=published');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/WeeklyMenus/Index')
        ->has('weeklyMenus.data', 1)
        ->where('weeklyMenus.data.0.status', 'published'));
});

it('weekly menu index data normalizalja az aktiv es datum szuroket', function (): void {
    $filters = WeeklyMenuIndexData::from([
        'active' => '1',
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-31',
        'per_page' => '100',
        'sort_field' => 'invalid',
        'sort_direction' => 'sideways',
    ]);

    expect($filters->active)->toBeTrue()
        ->and($filters->status)->toBe(WeeklyMenu::STATUS_PUBLISHED)
        ->and($filters->date_from)->toBe('2026-05-01')
        ->and($filters->date_to)->toBe('2026-05-31')
        ->and($filters->per_page)->toBe(50)
        ->and($filters->sort_field)->toBe('week_start')
        ->and($filters->sort_direction)->toBe('desc');
});

it('weekly menu date range filter mukodik', function (): void {
    $user = User::factory()->create();

    WeeklyMenu::factory()->create([
        'title' => 'Majusi menu',
        'week_start' => '2026-05-04',
        'week_end' => '2026-05-10',
    ]);
    WeeklyMenu::factory()->create([
        'title' => 'Juniusi menu',
        'week_start' => '2026-06-01',
        'week_end' => '2026-06-07',
    ]);

    $response = $this->actingAs($user)->get('/admin/weekly-menus?date_from=2026-05-01&date_to=2026-05-31');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/WeeklyMenus/Index')
        ->has('weeklyMenus.data', 1)
        ->where('weeklyMenus.data.0.title', 'Majusi menu'));
});

it('weekly menu publish mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    $menu = WeeklyMenu::factory()->create([
        'status' => WeeklyMenu::STATUS_DRAFT,
        'published_at' => null,
    ]);

    WeeklyMenuItem::factory()->create([
        'weekly_menu_id' => $menu->id,
        'product_id' => $product->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post("/admin/weekly-menus/{$menu->id}/publish");

    $response->assertRedirect('/admin/weekly-menus');

    $menu->refresh();
    expect($menu->status)->toBe(WeeklyMenu::STATUS_PUBLISHED);
    expect($menu->published_at)->not->toBeNull();
});

it('weekly menu publish csak egy aktiv menut hagy', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    $oldActive = WeeklyMenu::factory()->create([
        'status' => WeeklyMenu::STATUS_PUBLISHED,
        'published_at' => Carbon::now(),
    ]);
    $newMenu = WeeklyMenu::factory()->create(['status' => WeeklyMenu::STATUS_DRAFT]);

    WeeklyMenuItem::factory()->create([
        'weekly_menu_id' => $newMenu->id,
        'product_id' => $product->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post("/admin/weekly-menus/{$newMenu->id}/publish");

    $response->assertRedirect('/admin/weekly-menus');

    expect($newMenu->refresh()->status)->toBe(WeeklyMenu::STATUS_PUBLISHED)
        ->and($oldActive->refresh()->status)->toBe(WeeklyMenu::STATUS_ARCHIVED)
        ->and(WeeklyMenu::query()->where('status', WeeklyMenu::STATUS_PUBLISHED)->count())->toBe(1);
});

it('weekly menu unpublish mukodik', function (): void {
    $user = User::factory()->create();
    $menu = WeeklyMenu::factory()->create([
        'status' => WeeklyMenu::STATUS_PUBLISHED,
        'published_at' => Carbon::now(),
    ]);

    $response = $this->actingAs($user)->post("/admin/weekly-menus/{$menu->id}/unpublish");

    $response->assertRedirect('/admin/weekly-menus');

    $menu->refresh();
    expect($menu->status)->toBe(WeeklyMenu::STATUS_DRAFT);
    expect($menu->published_at)->toBeNull();
});

it('weekly menu item hozzaadas mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    $menu = WeeklyMenu::factory()->create();

    $response = $this->actingAs($user)->post("/admin/weekly-menus/{$menu->id}/items", [
        'product_id' => $product->id,
        'override_name' => 'Kovaszos premium',
        'override_price' => 2990,
        'sort_order' => 1,
        'is_active' => true,
        'badge_text' => 'Uj',
        'stock_note' => 'Limitalt',
    ]);

    $response->assertRedirect('/admin/weekly-menus');
    $this->assertDatabaseHas('weekly_menu_items', [
        'weekly_menu_id' => $menu->id,
        'product_id' => $product->id,
    ]);
});

it('weekly menu item store data flow price fallbacket ad listazashoz', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'is_active' => true,
        'price' => 1890,
    ]);
    $menu = WeeklyMenu::factory()->create();

    $response = $this->actingAs($user)->post("/admin/weekly-menus/{$menu->id}/items", [
        'product_id' => $product->id,
        'price_override' => null,
        'is_available' => true,
        'sort_order' => 3,
    ]);

    $response->assertRedirect('/admin/weekly-menus');

    $menu->refresh()->load('items.product.category');
    $data = WeeklyMenuListItemData::from($menu)->toArray();

    expect($data['items'][0]['price'])->toBe(1890.0)
        ->and($data['items'][0]['price_override'])->toBeNull()
        ->and($data['items'][0]['is_available'])->toBeTrue();
});

it('weekly menu item duplicate product nem adhato', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    $menu = WeeklyMenu::factory()->create();

    WeeklyMenuItem::factory()->create([
        'weekly_menu_id' => $menu->id,
        'product_id' => $product->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($user)->post("/admin/weekly-menus/{$menu->id}/items", [
        'product_id' => $product->id,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['product_id']);
});

it('weekly menu item update mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    $menu = WeeklyMenu::factory()->create();
    $item = WeeklyMenuItem::factory()->create([
        'weekly_menu_id' => $menu->id,
        'product_id' => $product->id,
        'category_id' => $category->id,
        'override_name' => null,
    ]);

    $response = $this->actingAs($user)->put("/admin/weekly-menus/{$menu->id}/items/{$item->id}", [
        'product_id' => $product->id,
        'override_name' => 'Atnevezett',
        'override_short_description' => 'Rovid',
        'override_price' => 1990,
        'sort_order' => 4,
        'is_active' => true,
        'badge_text' => null,
        'stock_note' => null,
    ]);

    $response->assertRedirect('/admin/weekly-menus');
    $this->assertDatabaseHas('weekly_menu_items', [
        'id' => $item->id,
        'override_name' => 'Atnevezett',
        'sort_order' => 4,
    ]);
});

it('weekly menu item update data flow kezeli az uj mezoket', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    $menu = WeeklyMenu::factory()->create();
    $item = WeeklyMenuItem::factory()->create([
        'weekly_menu_id' => $menu->id,
        'product_id' => $product->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($user)->put("/admin/weekly-menus/{$menu->id}/items/{$item->id}", [
        'product_id' => $product->id,
        'price_override' => 2090,
        'is_available' => false,
        'sort_order' => 8,
    ]);

    $response->assertRedirect('/admin/weekly-menus');

    $this->assertDatabaseHas('weekly_menu_items', [
        'id' => $item->id,
        'override_price' => '2090.00',
        'is_active' => false,
        'sort_order' => 8,
    ]);
});

it('weekly menu item delete mukodik', function (): void {
    $user = User::factory()->create();
    $item = WeeklyMenuItem::factory()->create();

    $response = $this->actingAs($user)->delete("/admin/weekly-menus/{$item->weekly_menu_id}/items/{$item->id}");

    $response->assertRedirect('/admin/weekly-menus');
    $this->assertDatabaseMissing('weekly_menu_items', ['id' => $item->id]);
});

it('weekly menu item csak aktiv product lehet', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $inactive = Product::factory()->create(['category_id' => $category->id, 'is_active' => false]);
    $menu = WeeklyMenu::factory()->create();

    $response = $this->actingAs($user)->post("/admin/weekly-menus/{$menu->id}/items", [
        'product_id' => $inactive->id,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['product_id']);
});
