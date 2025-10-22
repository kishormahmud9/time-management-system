<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $permissions = [
            'manage-roles',
            'create-roles',
            'view-details-roles',
            'update-roles',
            'delete-roles',
            'create-permissions',
            'view-details-permissions',
            'update-permissions',
            'delete-permissions',
            'manage-permissions',
            'view-profile',
            'edit-profile',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
        }

        $admin = Role::firstOrCreate(['name' => 'System Admin', 'guard_name' => 'api']);
        $business = Role::firstOrCreate(['name' => 'Business Admin', 'guard_name' => 'api']);
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'api']);
        $user = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'api']);

        // attach permissions to roles (example)
        $admin->givePermissionTo(Permission::all());
        $business->givePermissionTo(['view-profile', 'edit-profile', 'manage-roles', 'create-roles', 'view-details-roles', 'update-roles', 'delete-roles', 'create-permissions', 'view-details-permissions', 'update-permissions', 'delete-permissions', 'manage-permissions',]);
        $staff->givePermissionTo(['view-profile']);
        $user->givePermissionTo(['view-profile']);
    }
}
