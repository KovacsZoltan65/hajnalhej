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
        $status = fake()->boolean(85) ? Courier::STATUS_ACTIVE : Courier::STATUS_INACTIVE;

        return [
            'name' => fake()->name(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'status' => $status,
            'vehicle_type' => fake()->optional()->randomElement(VehicleType::values()),
            'active' => $status === Courier::STATUS_ACTIVE,
            'notes' => fake()->optional()->sentence(),
            'meta' => null,
        ];
    }
}
