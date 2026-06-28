<?php

namespace Database\Factories;

use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Factories\Factory;

class HeroSlideFactory extends Factory
{
    protected $model = HeroSlide::class;

    public function definition(): array
    {
        return [
            'title' => 'Ternak Organik Berkualitas Premium',
            'subtitle' => 'Sapi, Kambing, Ayam & Bebek Sehat dari Peternak Mitra Terpercaya',
            'image' => 'hero/slide-default.jpg',
            'button_text' => 'Lihat Koleksi',
            'button_url' => '#products',
            'button_target' => '_self',
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}