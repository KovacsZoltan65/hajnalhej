<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'HH-'.now()->format('Ymd').'-'.str_pad((string) fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => '+36'.fake()->numerify('30#######'),
            'status' => Order::STATUS_PENDING,
            'currency' => 'HUF',
            'subtotal' => 5000,
            'total' => 5000,
            'notes' => null,
            'pickup_date' => now()->toDateString(),
            'pickup_time_slot' => '08:00-10:00',
            'placed_at' => now(),
            'confirmed_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'internal_notes' => null,
            'metadata' => null,
        ];
    }
}
