<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'type' => 'payment',
                'label' => 'Menunggu Pembayaran',
                'label_en' => 'Awaiting Payment',
                'color' => 'amber',
                'sort_order' => 1,
                'is_terminal' => false,
            ],
            [
                'code' => 'paid',
                'type' => 'payment',
                'label' => 'Lunas',
                'label_en' => 'Paid',
                'color' => 'green',
                'sort_order' => 2,
                'is_terminal' => false,
            ],
            [
                'code' => 'payment_failed',
                'type' => 'payment',
                'label' => 'Pembayaran Gagal',
                'label_en' => 'Payment Failed',
                'color' => 'red',
                'sort_order' => 3,
                'is_terminal' => true,
            ],
            [
                'code' => 'payment_refunded',
                'type' => 'payment',
                'label' => 'Dana Dikembalikan',
                'label_en' => 'Refunded',
                'color' => 'gray',
                'sort_order' => 4,
                'is_terminal' => true,
            ],
        ];

        foreach ($statuses as $status) {
            OrderStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
