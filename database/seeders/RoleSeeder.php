<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage_users',
            'manage_all_documents',
            'create_documents',
            'view_own_documents',
            'delete_own_documents',
            'share_documents',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Admin role with all permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        // Create User role with limited permissions
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions([
            'create_documents',
            'view_own_documents',
            'delete_own_documents',
            'share_documents',
        ]);
    }
}
