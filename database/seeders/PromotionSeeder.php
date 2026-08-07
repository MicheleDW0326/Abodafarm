<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles seeder is run first
        $this->call(\Database\Seeders\RoleSeeder::class);

        $adminUser = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();

        if (!$adminUser) {
            $adminUser = User::first();
        }

        // Create admin user if none exists
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $adminUser->assignRole('admin');
        }

        $promotions = [
            [
                'name' => 'Diskon Selamat Datang',
                'code' => 'WELCOME10',
                'description' => 'Diskon 10% untuk pelanggan baru',
                'type' => 'percentage',
                'value' => 10,
                'minimum_purchase' => 100000,
                'maximum_discount' => 50000,
                'usage_limit' => 1000,
                'usage_per_user' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
                'status' => 'active',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Hemat Rp 50.000',
                'code' => 'HEMAT50000',
                'description' => 'Potongan harga Rp 50.000 untuk pembelian minimal Rp 200.000',
                'type' => 'fixed',
                'value' => 50000,
                'minimum_purchase' => 200000,
                'maximum_discount' => null,
                'usage_limit' => 500,
                'usage_per_user' => 2,
                'used_count' => 0,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(6),
                'status' => 'active',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Gratis Ongkir',
                'code' => 'ONGKIRGRATIS',
                'description' => 'Gratis biaya pengiriman untuk semua metode pembayaran',
                'type' => 'free_shipping',
                'value' => 0,
                'minimum_purchase' => 150000,
                'maximum_discount' => null,
                'usage_limit' => null,
                'usage_per_user' => 3,
                'used_count' => 0,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(3),
                'status' => 'active',
                'created_by' => $adminUser->id,
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
