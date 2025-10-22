<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleByUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the first user (System Admin)
        $user = User::where('email', 'superadmin@example.com')->first();

        if ($user) {
            // Find the System Admin role
            $adminRole = Role::where('name', 'System Admin')->first();

            if ($adminRole) {
                // Assign the role to the user
                $user->assignRole($adminRole);

                echo "✅ System Admin role assigned to superadmin@example.com\n";
            } else {
                echo "⚠️ 'System Admin' role not found. Please run RolePermissionSeeder first.\n";
            }
        } else {
            echo "⚠️ User 'superadmin@example.com' not found. Please run UserSeeder first.\n";
        }
    }
}
