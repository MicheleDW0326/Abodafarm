<?php

namespace Database\Seeders;

use App\Models\DeliveryArea;
use Illuminate\Database\Seeder;

class DeliveryAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            // Sulawesi Utara - Gratis Ongkir
            [
                'region_name' => 'Kota Manado',
                'city' => 'Manado',
                'province' => 'Sulawesi Utara',
                'fee' => 0,
                'estimated_days' => 1,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'region_name' => 'Kota Bitung',
                'city' => 'Bitung',
                'province' => 'Sulawesi Utara',
                'fee' => 0,
                'estimated_days' => 1,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'region_name' => 'Kota Tomohon',
                'city' => 'Tomohon',
                'province' => 'Sulawesi Utara',
                'fee' => 0,
                'estimated_days' => 1,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'region_name' => 'Kabupaten Minahasa',
                'city' => 'Tondano',
                'province' => 'Sulawesi Utara',
                'fee' => 0,
                'estimated_days' => 1,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'region_name' => 'Kepulauan Talaud',
                'city' => 'Melonguane',
                'province' => 'Sulawesi Utara',
                'fee' => 0,
                'estimated_days' => 2,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'region_name' => 'Kota Kotamobagu',
                'city' => 'Kotamobagu',
                'province' => 'Sulawesi Utara',
                'fee' => 50000,
                'estimated_days' => 2,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'region_name' => 'Kabupaten Bolmong',
                'city' => 'Lolak',
                'province' => 'Sulawesi Utara',
                'fee' => 75000,
                'estimated_days' => 2,
                'is_active' => true,
                'sort_order' => 7,
            ],

            // Luar Sulawesi Utara - Berbayar
            [
                'region_name' => 'Kota Gorontalo',
                'city' => 'Gorontalo',
                'province' => 'Gorontalo',
                'fee' => 150000,
                'estimated_days' => 3,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'region_name' => 'Kota Palu',
                'city' => 'Palu',
                'province' => 'Sulawesi Tengah',
                'fee' => 250000,
                'estimated_days' => 4,
                'is_active' => true,
                'sort_order' => 9,
            ],
        ];

        foreach ($areas as $area) {
            DeliveryArea::updateOrCreate(
                ['region_name' => $area['region_name']],
                $area
            );
        }
    }
}
