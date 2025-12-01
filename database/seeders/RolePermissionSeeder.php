<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Shop Management
            'view shops',
            'create shops',
            'edit shops',
            'delete shops',
            
            // Product Management
            'view products',
            'create products',
            'edit products',
            'delete products',
            
            // Order Management
            'view orders',
            'edit orders',
            'delete orders',
            
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Category Management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            
            // Role & Permission Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign roles',
            
            // Settings
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view shops',
            'create shops',
            'edit shops',
            'view products',
            'create products',
            'edit products',
            'view orders',
            'edit orders',
            'view users',
            'view categories',
            'create categories',
            'edit categories',
        ]);

        $shopOwner = Role::create(['name' => 'shop-owner']);
        $shopOwner->givePermissionTo([
            'view shops',
            'edit shops',
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view orders',
            'edit orders',
            'view categories',
            'create categories',
            'edit categories',
        ]);

        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo([
            'view products',
            'view shops',
        ]);
    }
}
