<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sapi',
                'description' => 'Ternak sapi organik berkualitas tinggi untuk aqiqah, kurban, dan kebutuhan bisnis. Dipelihara dengan pakan organik dan pemeriksaan kesehatan rutin.',
                'slug' => 'sapi',
                'icon' => 'fa-solid fa-cow',
                'image' => 'categories/sapi.jpg',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Kambing',
                'description' => 'Kambing pilihan untuk aqiqah, kurban, dan usaha peternakan.品种 unggul dengan pertumbuhan optimal dan daging berkualitas.',
                'slug' => 'kambing',
                'icon' => 'fa-solid fa-sheep',
                'image' => 'categories/kambing.jpg',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Ayam',
                'description' => 'Ayam organik pedaging dan petelur yang dipelihara secara alami tanpa hormon pertumbuhan. Daging lebih sehat dan flavours.',
                'slug' => 'ayam',
                'icon' => 'fa-solid fa-egg',
                'image' => 'categories/ayam.jpg',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Bebek',
                'description' => 'Bebek petelur dan pedaging berkualitas untuk kebutuhan rumah tangga dan kuliner. Pemeliharaan dengan sistem organik.',
                'slug' => 'bebek',
                'icon' => 'fa-solid fa-dove',
                'image' => 'categories/bebek.jpg',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Daging Segar',
                'description' => 'Potongan daging segar pilihan dari ternak organik. Diproses dengan standar hygiene tinggi dan langsung dikirim ke tujuan.',
                'slug' => 'daging-segar',
                'icon' => 'fa-solid fa-drumstick-bite',
                'image' => 'categories/daging.jpg',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
