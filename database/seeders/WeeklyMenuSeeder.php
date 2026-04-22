<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class WeeklyMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $weekStart = Carbon::today()->startOfWeek();
        $weekEnd = Carbon::today()->endOfWeek();

        $menu = WeeklyMenu::query()->updateOrCreate(
            ['slug' => 'aktualis-heti-menu'],
            [
                'title' => 'Aktuális heti menü',
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'status' => WeeklyMenu::STATUS_PUBLISHED,
                'public_note' => 'A rendeleseket csutortok estig varjuk.',
                'internal_note' => null,
                'is_featured' => true,
                'published_at' => Carbon::now(),
            ],
        );

        $products = Product::query()->where('is_active', true)->take(3)->get();

        foreach ($products as $index => $product) {
            WeeklyMenuItem::query()->updateOrCreate(
                [
                    'weekly_menu_id' => $menu->id,
                    'product_id' => $product->id,
                ],
                [
                    'category_id' => $product->category_id,
                    'override_name' => null,
                    'override_short_description' => null,
                    'override_price' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'badge_text' => $index === 0 ? 'Nepszeru' : null,
                    'stock_note' => null,
                ],
            );
        }
    }
}
