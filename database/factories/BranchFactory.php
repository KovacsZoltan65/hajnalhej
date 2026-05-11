<?php

namespace Database\Factories;

use App\Data\Branches\BranchType;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' '.fake()->unique()->numerify('###'),
            'code' => fake()->unique()->bothify('BR-###'),
            'type' => fake()->randomElement(BranchType::values()),
            'email' => fake()->optional()->companyEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'active' => fake()->boolean(85),
            'meta' => null,
        ];
    }
}
