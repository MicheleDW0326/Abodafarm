<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper($this->faker->unique()->bothify('????????##??')) . '-' . date('Ymd'),
            'order_type' => $this->faker->randomElement(['cart', 'inquiry']),
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_address' => $this->faker->address(),
            'notes' => $this->faker->optional()->sentence(),
            'total_items' => $this->faker->numberBetween(1, 10),
            'total_amount' => $this->faker->randomFloat(2, 100000, 50000000),
            'discount_amount' => 0,
            'final_amount' => $this->faker->randomFloat(2, 100000, 50000000),
            'status' => 'pending',
            'placed_at' => now(),
            'preferred_delivery_date' => $this->faker->optional()->dateTimeBetween('+1 week', '+1 month'),
            'pickup_location' => $this->faker->optional()->address(),
            'admin_notes' => null,
        ];
    }

    public function cart(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'cart',
        ]);
    }

    public function inquiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'inquiry',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
