<?php

namespace Database\Factories;

use App\Enums\Delivery\VehicleType;
use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'vehicle_type' => fake()->optional()->randomElement(VehicleType::values()),
            'active' => fake()->boolean(85),
            'notes' => fake()->optional()->sentence(),
            'meta' => null,
        ];
    }
}
