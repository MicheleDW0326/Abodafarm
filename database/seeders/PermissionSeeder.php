<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Role Matrix:
     *
     * | Permission                | Admin | Staff | Farmer | Customer |
     * |--------------------------|-------|-------|--------|----------|
     * | ORDERS                   |       |       |        |          |
     * | orders.view              |   X   |   X   |   X    |    X     |
     * | orders.view-own          |   -   |   -   |   -    |    X     |
     * | orders.create            |   X   |   X   |   -    |    X     |
     * | orders.update            |   X   |   X   |   X    |    -     |
     * | orders.update-own        |   -   |   -   |   -    |    X     |
     * | orders.cancel            |   X   |   X   |   -    |    X     |
     * | orders.delete            |   X   |   -   |   -    |    -     |
     * | orders.approve           |   X   |   X   |   X    |    -     |
     * | orders.reject            |   X   |   X   |   X    |    -     |
     * | orders.process          |   X   |   X   |   X    |    -     |
     * | orders.ship              |   X   |   X   |   X    |    -     |
     * | orders.deliver           |   X   |   X   |   X    |    -     |
     * | orders.complete          |   X   |   X   |   -    |    X     |
     * |--------------------------|-------|-------|--------|----------|
     * | PRODUCTS                 |       |       |        |          |
     * | products.view            |   X   |   X   |   X    |    X     |
     * | products.create          |   X   |   X   |   X    |    -     |
     * | products.update          |   X   |   X   |   X    |    -     |
     * | products.update-own      |   -   |   -   |   X    |    -     |
     * | products.delete          |   X   |   X   |   X    |    -     |
     * | products.delete-own     |   -   |   -   |   X    |    -     |
     * |--------------------------|-------|-------|--------|----------|
     * | REVIEWS                  |       |       |        |          |
     * | reviews.view             |   X   |   X   |   X    |    X     |
     * | reviews.create           |   X   |   X   |   X    |    X     |
     * | reviews.update           |   X   |   X   |   X    |    X     |
     * | reviews.update-own      |   -   |   -   |   -    |    X     |
     * | reviews.delete           |   X   |   X   |   X    |    X     |
     * | reviews.delete-own     |   -   |   -   |   -    |    X     |
     * | reviews.moderate        |   X   |   X   |   -    |    -     |
     * | reviews.flag             |   X   |   X   |   X    |    -     |
     * |--------------------------|-------|-------|--------|----------|
     * | FARM PROFILES            |       |       |        |          |
     * | farm-profiles.view       |   X   |   X   |   X    |    X     |
     * | farm-profiles.create     |   X   |   X   |   X    |    -     |
     * | farm-profiles.update     |   X   |   X   |   X    |    -     |
     * | farm-profiles.update-own|   -   |   -   |   X    |    -     |
     * | farm-profiles.delete     |   X   |   X   |   X    |    -     |
     * |--------------------------|-------|-------|--------|----------|
     * | ADDRESSES                |       |       |        |          |
     * | addresses.view          |   X   |   X   |   X    |    X     |
     * | addresses.view-own      |   -   |   -   |   -    |    X     |
     * | addresses.create        |   X   |   X   |   X    |    X     |
     * | addresses.update        |   X   |   X   |   X    |    X     |
     * | addresses.update-own   |   -   |   -   |   -    |    X     |
     * | addresses.delete        |   X   |   X   |   X    |    X     |
     * | addresses.delete-own   |   -   |   -   |   -    |    X     |
     * |--------------------------|-------|-------|--------|----------|
     * | USERS                    |       |       |        |          |
     * | users.view              |   X   |   X   |   -    |    -     |
     * | users.create            |   X   |   X   |   -    |    -     |
     * | users.update            |   X   |   X   |   -    |    -     |
     * | users.delete            |   X   |   -   |   -    |    -     |
     * |--------------------------|-------|-------|--------|----------|
     * | CATEGORIES               |       |       |        |          |
     * | categories.view          |   X   |   X   |   X    |    X     |
     * | categories.create        |   X   |   X   |   -    |    -     |
     * | categories.update        |   X   |   X   |   -    |    -     |
     * | categories.delete        |   X   |   -   |   -    |    -     |
     * |--------------------------|-------|-------|--------|----------|
     * | REPORTS                  |       |       |        |          |
     * | reports.view            |   X   |   X   |   X    |    -     |
     * | reports.export          |   X   |   X   |   -    |    -     |
     * |--------------------------|-------|-------|--------|----------|
     * | SETTINGS                 |       |       |        |          |
     * | settings.view            |   X   |   X   |   -    |    -     |
     * | settings.update         |   X   |   -   |   -    |    -     |
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Orders
            'orders.view',
            'orders.view-own',
            'orders.create',
            'orders.update',
            'orders.update-own',
            'orders.cancel',
            'orders.delete',
            'orders.approve',
            'orders.reject',
            'orders.process',
            'orders.ship',
            'orders.deliver',
            'orders.complete',

            // Products
            'products.view',
            'products.create',
            'products.update',
            'products.update-own',
            'products.delete',
            'products.delete-own',

            // Reviews
            'reviews.view',
            'reviews.create',
            'reviews.update',
            'reviews.update-own',
            'reviews.delete',
            'reviews.delete-own',
            'reviews.moderate',
            'reviews.flag',

            // Farm Profiles
            'farm-profiles.view',
            'farm-profiles.create',
            'farm-profiles.update',
            'farm-profiles.update-own',
            'farm-profiles.delete',

            // Addresses
            'addresses.view',
            'addresses.view-own',
            'addresses.create',
            'addresses.update',
            'addresses.update-own',
            'addresses.delete',
            'addresses.delete-own',

            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Categories
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            // Reports
            'reports.view',
            'reports.export',

            // Settings
            'settings.view',
            'settings.update',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Get roles
        $admin = Role::findByName('admin', 'web');
        $staf = Role::findByName('staf', 'web');
        $peternak = Role::findByName('peternak', 'web');
        $pelanggan = Role::findByName('pelanggan', 'web');

        // Admin: Full access
        $admin->givePermissionTo(Permission::all());

        // Staff: Operational tasks only - no management capabilities
        // Per requirements:
        // CAN: View assigned orders, View order details, View customer info, View shipping info,
        //      Update shipping status, Upload delivery proof (future), View notifications, Update own profile
        // CANNOT: Manage products, Manage categories, Manage users, Manage roles, Manage permissions,
        //         Manage bookings, Manage finances, Manage reports, Import Excel, Export Finance,
        //         Delete orders, Refund payments, Change product stock, Approve livestock booking
        $stafPermissions = [
            // Orders - View and update shipping status only
            'orders.view',
            'orders.update', // Update shipping status
            'orders.process',
            'orders.ship',
            'orders.deliver',
            // Products - View only, no management
            'products.view',
            // Reviews - Moderate assigned reviews
            'reviews.view',
            'reviews.moderate',
            // Farm Profiles - View only
            'farm-profiles.view',
            // Addresses - View customer addresses for delivery
            'addresses.view',
            // Settings - View and update own profile only
            'settings.view',
        ];
        $staf->givePermissionTo($stafPermissions);

        // Farmer (Peternak): Manage own products and related orders
        $farmerPermissions = [
            // Orders
            'orders.view', 'orders.update', 'orders.approve', 'orders.reject',
            'orders.process', 'orders.ship', 'orders.deliver',
            // Products
            'products.view', 'products.create', 'products.update', 'products.update-own',
            'products.delete', 'products.delete-own',
            // Reviews
            'reviews.view', 'reviews.create', 'reviews.update', 'reviews.update-own',
            'reviews.delete', 'reviews.delete-own', 'reviews.flag',
            // Farm Profiles
            'farm-profiles.view', 'farm-profiles.create', 'farm-profiles.update',
            'farm-profiles.update-own', 'farm-profiles.delete',
            // Addresses
            'addresses.view', 'addresses.create', 'addresses.update', 'addresses.delete',
            // Categories
            'categories.view',
            // Reports
            'reports.view',
        ];
        $peternak->givePermissionTo($farmerPermissions);

        // Customer (Pelanggan): Manage own orders and reviews
        $customerPermissions = [
            // Orders
            'orders.view-own', 'orders.create', 'orders.update-own', 'orders.cancel',
            'orders.complete',
            // Products
            'products.view',
            // Reviews
            'reviews.view', 'reviews.create', 'reviews.update-own', 'reviews.delete-own',
            // Farm Profiles
            'farm-profiles.view',
            // Addresses
            'addresses.view-own', 'addresses.create', 'addresses.update-own', 'addresses.delete-own',
            // Categories
            'categories.view',
        ];
        $pelanggan->givePermissionTo($customerPermissions);

        $this->command->info('Permissions seeded successfully!');
        $this->command->table(['Role', 'Permission Count'], [
            ['admin', $admin->permissions->count()],
            ['staf', $staf->permissions->count()],
            ['peternak', $peternak->permissions->count()],
            ['pelanggan', $pelanggan->permissions->count()],
        ]);
    }
}
