<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'role' => $this->faker->randomElement(['Ibu Rumah Tangga', 'Pengusaha', 'Food Blogger', 'Peternak', 'Catering Owner']),
            'content' => $this->faker->paragraph(3),
            'image' => null,
            'rating' => $this->faker->numberBetween(4, 5),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}