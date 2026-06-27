<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            TestimonialSeeder::class,
            FaqSeeder::class,
            HeroSlideSeeder::class,
            FarmStatisticSeeder::class,
            DeliveryAreaSeeder::class,
        ]);

        // Create default users
        $users = [
            [
                'name' => 'Royland',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Michele',
                'email' => 'staf@gmail.com',
                'role' => 'staf',
            ],
            [
                'name' => 'Johan Pakpahan',
                'email' => 'peternak@gmail.com',
                'role' => 'peternak',
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'Lorto@gmail.com',
                'role' => 'pelanggan',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$userData['role']]);
        }
    }
}