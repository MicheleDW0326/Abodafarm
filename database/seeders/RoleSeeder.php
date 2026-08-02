<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     * Note: Permissions are seeded in PermissionSeeder which should be called after this.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'staf', 'peternak', 'pelanggan'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $this->command->info('Roles seeded: admin, staf, peternak, pelanggan');
    }
}
