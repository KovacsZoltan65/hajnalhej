<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

it('public weekly menu published menu megjelenik', function (): void {
    $category = Category::factory()->create(['name' => 'Kenyerek', 'is_active' => true]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Klasszikus',
        'price' => 2450,
        'is_active' => true,
    ]);

    $menu = WeeklyMenu::factory()->create([
        'title' => 'Aktuális menü',
        'status' => WeeklyMenu::STATUS_PUBLISHED,
        'week_start' => Carbon::today()->startOfWeek(),
        'week_end' => Carbon::today()->endOfWeek(),
        'published_at' => Carbon::now(),
    ]);

    WeeklyMenuItem::factory()->create([
        'weekly_menu_id' => $menu->id,
        'product_id' => $product->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = $this->get('/weekly-menu');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('WeeklyMenu')
        ->where('menu.title', 'Aktuális menü')
        ->where('fallback_used', false));
});

it('public weekly menu override mezok elsobbseget elveznek', function (): void {
    $category = Category::factory()->create(['name' => 'Kenyerek', 'is_active' => true]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Alap nev',
        'short_description' => 'Alap leiras',
        'price' => 1900,
        'is_active' => true,
    ]);

    $menu = WeeklyMenu::factory()->create([
        'status' => WeeklyMenu::STATUS_PUBLISHED,
        'week_start' => Carbon::today()->startOfWeek(),
        'week_end' => Carbon::today()->endOfWeek(),
        'published_at' => Carbon::now(),
    ]);

    WeeklyMenuItem::factory()->create([
        'weekly_menu_id' => $menu->id,
        'product_id' => $product->id,
        'category_id' => $category->id,
        'override_name' => 'Override nev',
        'override_short_description' => 'Override leiras',
        'override_price' => 2600,
        'is_active' => true,
    ]);

    $response = $this->get('/weekly-menu');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('WeeklyMenu')
        ->where('groups.0.items.0.name', 'Override nev')
        ->where('groups.0.items.0.short_description', 'Override leiras')
        ->where('groups.0.items.0.price', 2600));
});

it('public weekly menu empty state ha nincs published menu', function (): void {
    WeeklyMenu::factory()->create([
        'status' => WeeklyMenu::STATUS_DRAFT,
        'published_at' => null,
    ]);

    $response = $this->get('/weekly-menu');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('WeeklyMenu')
        ->where('menu', null)
        ->where('groups', []));
});
