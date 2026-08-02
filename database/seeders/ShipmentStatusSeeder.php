<?php

namespace Database\Seeders;

use App\Models\ShipmentStatus;
use Illuminate\Database\Seeder;

class ShipmentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'code' => 'processing',
                'label' => 'Sedang Diproses',
                'label_en' => 'Processing',
                'color' => 'blue',
                'icon' => 'fa-box',
                'sort_order' => 1,
                'tracking_url_template' => null,
                'customer_notified' => true,
            ],
            [
                'code' => 'shipped',
                'label' => 'Dalam Pengiriman',
                'label_en' => 'Shipped',
                'color' => 'purple',
                'icon' => 'fa-truck',
                'sort_order' => 2,
                'tracking_url_template' => null,
                'customer_notified' => true,
            ],
            [
                'code' => 'delivered',
                'label' => 'Telah Diterima',
                'label_en' => 'Delivered',
                'color' => 'emerald',
                'icon' => 'fa-home',
                'sort_order' => 3,
                'tracking_url_template' => null,
                'customer_notified' => true,
            ],
            [
                'code' => 'completed',
                'label' => 'Selesai',
                'label_en' => 'Completed',
                'color' => 'green',
                'icon' => 'fa-check-double',
                'sort_order' => 4,
                'tracking_url_template' => null,
                'customer_notified' => true,
            ],
            [
                'code' => 'cancelled',
                'label' => 'Dibatalkan',
                'label_en' => 'Cancelled',
                'color' => 'gray',
                'icon' => 'fa-ban',
                'sort_order' => 5,
                'tracking_url_template' => null,
                'customer_notified' => true,
            ],
        ];

        foreach ($statuses as $status) {
            ShipmentStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
