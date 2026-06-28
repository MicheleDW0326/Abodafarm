<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $type = $this->faker->randomElement(['live', 'fresh']);

        return [
            'category_id' => Category::factory(),
            'owner_id' => User::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(3),
            'price' => $this->faker->numberBetween(50000, 25000000),
            'image' => 'products/default.jpg',
            'images' => [],
            'type' => $type,
            'status' => $this->faker->randomElement(['available', 'pre_order', 'in_care']),
            'stock' => $this->faker->numberBetween(0, 100),
            'weight' => $type === 'live' ? $this->faker->randomFloat(2, 100, 500) : null,
            'age' => $type === 'live' ? $this->faker->randomElement(['1 Tahun', '1.5 Tahun', '2 Tahun', '2.5 Tahun']) : null,
            'sex' => $type === 'live' ? $this->faker->randomElement(['male', 'female', 'mixed']) : null,
            'origin' => 'Sulawesi Utara',
            'condition' => 'Sehat',
            'weight_per_unit' => $type === 'fresh' ? $this->faker->randomFloat(2, 0.5, 2) : null,
            'is_featured' => $this->faker->boolean(20),
        ];
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'live',
            'weight' => $this->faker->randomFloat(2, 100, 500),
            'age' => $this->faker->randomElement(['1 Tahun', '1.5 Tahun', '2 Tahun', '2.5 Tahun']),
            'sex' => $this->faker->randomElement(['male', 'female', 'mixed']),
        ]);
    }

    public function fresh(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fresh',
            'weight_per_unit' => $this->faker->randomFloat(2, 0.5, 2),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}