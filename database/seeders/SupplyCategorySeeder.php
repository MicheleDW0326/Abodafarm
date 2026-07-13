<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class SupplyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pakan Ternak',
                'description' => 'Pakan berkualitas tinggi untuk sapi, kambing, ayam, dan bebek. Mengandung nutrisi lengkap untuk pertumbuhan optimal.',
                'slug' => 'pakan-ternak',
                'icon' => 'fa-solid fa-wheat-awn',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Vitamin & Suplemen',
                'description' => 'Vitamin dan suplemen untuk menjaga kesehatan dan produktivitas ternak. Premix dan feed additive berkualitas.',
                'slug' => 'vitamin-suplemen',
                'icon' => 'fa-solid fa-capsules',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Obat-Obatan',
                'description' => 'Obat-obatan untuk pencegahan dan pengobatan penyakit pada ternak. Antiseptik, antibiotik, dan antiparasit.',
                'slug' => 'obat-obatan',
                'icon' => 'fa-solid fa-syringe',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Alat Minum',
                'description' => 'Peralatan minum otomatis dan manual untuk ternak. Nipple drinker, galon minum, dan fountain drinker.',
                'slug' => 'alat-minum',
                'icon' => 'fa-solid fa-faucet-drip',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Alat Makan',
                'description' => 'Peralatan makan dan feeding untuk ternak. Feeding trough, tempat makan ayam, dan feeder otomatis.',
                'slug' => 'alat-makan',
                'icon' => 'fa-solid fa-bowl-food',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Alat Kebersihan',
                'description' => 'Peralatan kebersihan dan disinfeksi kandang. Sprayer, sapu, alat pembersih, dan desinfektan.',
                'slug' => 'alat-kebersihan',
                'icon' => 'fa-solid fa-broom',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Perlengkapan Kandang',
                'description' => 'Perlengkapan tambahan untuk kandang. Lampu pemanas, kipas angin, sensor suhu, dan aksesoris kandang.',
                'slug' => 'perlengkapan-kandang',
                'icon' => 'fa-solid fa-warehouse',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Lainnya',
                'description' => 'Produk dan peralatan peternakan lainnya yang tidak termasuk dalam kategori di atas.',
                'slug' => 'lainnya',
                'icon' => 'fa-solid fa-ellipsis-h',
                'is_active' => true,
                'sort_order' => 8,
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
