<?php

namespace Database\Factories;

use App\Models\FarmProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FarmProfileFactory extends Factory
{
    protected $model = FarmProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'farm_name' => $this->faker->company() . ' Farm',
            'description' => $this->faker->paragraph(3),
            'address' => $this->faker->streetAddress(),
            'phone' => '08' . $this->faker->numerify('##########'),
            'village' => $this->faker->randomElement(['Woloan', 'Kampung Jawa', 'Tondano']),
            'district' => $this->faker->randomElement(['Tombulu', 'Tombariri', 'Kawangkoan']),
            'regency' => 'Minahasa',
            'province' => 'Sulawesi Utara',
            'image' => 'farm-profile/default.jpg',
            'cover_image' => 'farm-profile/cover-default.jpg',
            'logo' => 'farm-profile/logo-default.png',
            'rating' => $this->faker->randomFloat(2, 3.5, 5),
            'total_reviews' => $this->faker->numberBetween(0, 100),
            'is_verified' => $this->faker->boolean(50),
            'latitude' => $this->faker->latitude(1.2, 1.8),
            'longitude' => $this->faker->longitude(124.3, 125.0),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }
}