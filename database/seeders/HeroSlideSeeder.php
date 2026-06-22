<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slides = [
            [
                'title' => 'Ternak Organik Berkualitas Premium',
                'subtitle' => 'Sapi, Kambing, Ayam & Bebek Sehat dari Peternak Mitra Terpercaya di Sulawesi Utara',
                'image' => 'hero/slide-1.jpg',
                'button_text' => 'Lihat Koleksi',
                'button_url' => '#products',
                'button_target' => '_self',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Transparansi Penuh - Dari Farm ke Meja Anda',
                'subtitle' => 'Pantau kondisi ternak, feeding schedule, dan hasil kesehatan sebelum memutuskan',
                'image' => 'hero/slide-2.jpg',
                'button_text' => 'Booking Ternak',
                'button_url' => '#contact',
                'button_target' => '_self',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Pengiriman Aman ke Rumah, Restoran & Bisnis Anda',
                'subtitle' => 'Layanan delivery Express untuk kebutuhan rumah tangga, HORECA, dan catering',
                'image' => 'hero/slide-3.jpg',
                'button_text' => 'Hubungi Kami',
                'button_url' => 'https://wa.me/6281234567890',
                'button_target' => '_blank',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($slides as $slide) {
            HeroSlide::updateOrCreate(
                ['title' => $slide['title']],
                $slide
            );
        }
    }
}
