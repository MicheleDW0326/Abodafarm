<?php

namespace Database\Factories;

use App\Models\FarmStatistic;
use Illuminate\Database\Eloquent\Factories\Factory;

class FarmStatisticFactory extends Factory
{
    protected $model = FarmStatistic::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(2),
            'value' => $this->faker->numberBetween(50, 500) . '+',
            'label' => $this->faker->words(3, true),
            'icon' => 'fa-solid fa-cow',
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}