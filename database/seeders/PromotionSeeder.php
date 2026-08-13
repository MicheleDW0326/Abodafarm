<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $promotions = [
            [
                'title' => 'Diskon Selamat Datang',
                'code' => 'WELCOME10',
                'description' => 'Diskon 10% untuk pelanggan baru',
                'type' => 'percentage',
                'discount_value' => 10,
                'min_purchase_amount' => 100000,
                'max_discount_amount' => 50000,
                'usage_limit' => 1000,
                'per_user_limit' => 1,
                'usage_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
            [
                'title' => 'Hemat Rp 50.000',
                'code' => 'HEMAT50000',
                'description' => 'Potongan harga Rp 50.000 untuk pembelian minimal Rp 200.000',
                'type' => 'fixed',
                'discount_value' => 50000,
                'min_purchase_amount' => 200000,
                'max_discount_amount' => null,
                'usage_limit' => 500,
                'per_user_limit' => 2,
                'usage_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
        ];

        foreach ($promotions as $promotion) {
            Promotion::updateOrCreate(
                ['code' => $promotion['code']],
                $promotion
            );
        }

        $this->command->info('Promotions seeded successfully!');
        $this->command->info('Created promotions: ' . implode(', ', array_column($promotions, 'code')));
    }
}
