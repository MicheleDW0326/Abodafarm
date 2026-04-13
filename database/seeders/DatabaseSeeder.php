<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        ]);

        $users = [
            [
                'name' => 'Royland',
                'email' => 'royland@freshgo.test',
                'role' => 'admin',
            ],
            [
                'name' => 'Michele',
                'email' => 'michele@freshgo.test',
                'role' => 'staf',
            ],
            [
                'name' => 'Esau',
                'email' => 'esau@freshgo.test',
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
