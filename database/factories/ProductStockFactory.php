<?php

namespace Database\Factories;

use App\Models\ProductStock;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductStockFactory extends Factory
{
    protected $model = ProductStock::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(10, 100),
            'supplier' => $this->faker->company(),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}