<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => $this->faker->randomElement(['Rumah', 'Kantor', 'Toko']),
            'recipient_name' => $this->faker->name(),
            'phone' => '08' . $this->faker->numerify('##########'),
            'address' => $this->faker->streetAddress(),
            'province' => 'Sulawesi Utara',
            'city' => $this->faker->randomElement(['Manado', 'Bitung', 'Tomohon', 'Minahasa']),
            'district' => $this->faker->randomElement(['Sario', 'Wanea', 'Tikala', 'Mapanget']),
            'postal_code' => $this->faker->postcode(),
            'is_default' => false,
            'is_active' => true,
            'latitude' => $this->faker->latitude(1.2, 1.8),
            'longitude' => $this->faker->longitude(124.3, 125.0),
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}