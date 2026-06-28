<?php

namespace Database\Factories;

use App\Models\DeliveryArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryAreaFactory extends Factory
{
    protected $model = DeliveryArea::class;

    public function definition(): array
    {
        return [
            'region_name' => 'Kota ' . $this->faker->city(),
            'city' => $this->faker->city(),
            'province' => $this->faker->randomElement(['Sulawesi Utara', 'Gorontalo', 'Sulawesi Tengah']),
            'fee' => $this->faker->randomElement([0, 25000, 50000, 75000, 100000]),
            'estimated_days' => $this->faker->numberBetween(1, 5),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 20),
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'fee' => 0,
        ]);
    }
}