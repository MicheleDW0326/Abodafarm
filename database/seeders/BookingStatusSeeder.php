<?php

namespace Database\Seeders;

use App\Models\BookingStatus;
use Illuminate\Database\Seeder;

class BookingStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'label' => 'Menunggu Persetujuan',
                'label_en' => 'Awaiting Approval',
                'color' => 'amber',
                'icon' => 'fa-clock',
                'sort_order' => 1,
                'requires_action' => true,
                'is_cancelled' => false,
            ],
            [
                'code' => 'approved',
                'label' => 'Disetujui',
                'label_en' => 'Approved',
                'color' => 'green',
                'icon' => 'fa-check-circle',
                'sort_order' => 2,
                'requires_action' => false,
                'is_cancelled' => false,
            ],
            [
                'code' => 'rejected',
                'label' => 'Ditolak',
                'label_en' => 'Rejected',
                'color' => 'red',
                'icon' => 'fa-times-circle',
                'sort_order' => 3,
                'requires_action' => false,
                'is_cancelled' => true,
            ],
        ];

        foreach ($statuses as $status) {
            BookingStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
