<?php

use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

function publishProductForOrdering(Product $product): WeeklyMenuItem
{
    $menu = WeeklyMenu::factory()->create([
        'status' => WeeklyMenu::STATUS_PUBLISHED,
        'week_start' => Carbon::today()->startOfWeek(),
        'week_end' => Carbon::today()->endOfWeek(),
        'published_at' => Carbon::now(),
    ]);

    return WeeklyMenuItem::factory()->create([
        'weekly_menu_id' => $menu->id,
        'product_id' => $product->id,
        'category_id' => $product->category_id,
        'is_active' => true,
    ]);
}
