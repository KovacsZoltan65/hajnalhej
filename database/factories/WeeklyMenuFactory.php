<?php

namespace Database\Factories;

use App\Models\WeeklyMenu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<WeeklyMenu>
 */
class WeeklyMenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::instance(fake()->dateTimeBetween('-4 weeks', '+4 weeks'))->startOfWeek();
        $end = $start->copy()->endOfWeek();
        $title = 'Heti menu - '.$start->format('Y.m.d');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'week_start' => $start->toDateString(),
            'week_end' => $end->toDateString(),
            'status' => WeeklyMenu::STATUS_DRAFT,
            'public_note' => fake()->optional()->sentence(8),
            'internal_note' => fake()->optional()->sentence(10),
            'is_featured' => fake()->boolean(20),
            'published_at' => null,
        ];
    }
}
