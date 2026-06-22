<?php

namespace Database\Seeders;

use App\Models\FarmStatistic;
use Illuminate\Database\Seeder;

class FarmStatisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statistics = [
            [
                'key' => 'ternak_terjual',
                'value' => '500+',
                'label' => 'Ternak Terjual',
                'icon' => 'fa-solid fa-cow',
                'sort_order' => 1,
            ],
            [
                'key' => 'pelanggan',
                'value' => '100+',
                'label' => 'Pelanggan Puas',
                'icon' => 'fa-solid fa-users',
                'sort_order' => 2,
            ],
            [
                'key' => 'peternak_mitra',
                'value' => '50+',
                'label' => 'Peternak Mitra',
                'icon' => 'fa-solid fa-handshake',
                'sort_order' => 3,
            ],
            [
                'key' => 'kepuasan',
                'value' => '98%',
                'label' => 'Tingkat Kepuasan',
                'icon' => 'fa-solid fa-star',
                'sort_order' => 4,
            ],
        ];

        foreach ($statistics as $stat) {
            FarmStatistic::updateOrCreate(
                ['key' => $stat['key']],
                $stat
            );
        }
    }
}
