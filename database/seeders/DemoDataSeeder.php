<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessPermission;
use App\Models\Holiday;
use App\Models\InternalUser;
use App\Models\Party;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\TimesheetDefault;
use App\Models\TimesheetEntry;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Business
        $business = Business::create([
            'name' => 'Demo Tech Solutions',
            'email' => 'info@demotech.com',
            'phone' => '+1234567890',
            'address' => '123 Tech Street, Silicon Valley, CA',
            'logo' => null,
            'slug' => 'demo-tech-solutions',
            'owner_id' => null, // Will update after creating owner
            'status' => 'active',
        ]);

        // Create Default Business Permissions
        BusinessPermission::create([
            'business_id' => $business->id,
            'user_can_login' => true,
            'commission' => true,
            'template_can_add' => true,
            'qb_integration' => true,
            'user_limit' => 10,
        ]);

        // Create Roles (if not exists)
        $systemAdminRole = Role::firstOrCreate(['name' => 'System Admin']);
        $businessAdminRole = Role::firstOrCreate(['name' => 'Business Admin']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);
        $userRole = Role::firstOrCreate(['name' => 'User']);

        // Create Permissions
        $permissions = [
            // User Management
            'create_user',
            'view_user',
            'update_user',
            'status_update_user',
            'delete_user',

            // Timesheet Management
            'create_timesheet',
            'view_timesheet',
            'update_timesheet',
            'delete_timesheet',
            'approve_timesheet',
            'submit_timesheet',

            // Party Management
            'create_party',
            'view_party',
            'update_party',
            'delete_party',

            // Project Management
            'create_project',
            'view_project',
            'update_project',
            'delete_project',

            // Business Management
            'create_business',
            'view_business',
            'update_business',
            'delete_business',

            // Role & Permission Management
            'manage_roles',
            'manage_permissions',

            // Reports
            'view_reports',
            'export_reports',

            // User Management
            'create_internal_user',
            'view_internal_user',
            'update_internal_user',
            'delete_internal_user',
            'status_update_internal_user',
            'role_update_internal_user',

            // User Details Management
            'create_user_details',
            'view_user_details',
            'update_user_details',
            'delete_user_details',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign Permissions to Roles

        // System Admin - All permissions
        $systemAdminRole->syncPermissions(Permission::all());

        // Business Admin - Most permissions except business management
        $businessAdminRole->syncPermissions([
            'create_user',
            'view_user',
            'update_user',
            'delete_user',
            'create_timesheet',
            'view_timesheet',
            'update_timesheet',
            'delete_timesheet',
            'approve_timesheet',
            'submit_timesheet',
            'create_party',
            'view_party',
            'update_party',
            'delete_party',
            'create_project',
            'view_project',
            'update_project',
            'delete_project',
            'manage_roles',
            'view_reports',
            'export_reports',
            'create_internal_user',
            'view_internal_user',
            'update_internal_user',
            'delete_internal_user',
            'status_update_internal_user',
            'role_update_internal_user',
            'create_user_details',
            'view_user_details',
            'update_user_details',
            'delete_user_details',

        ]);

        // Staff - Limited permissions
        $staffRole->syncPermissions([
            'create_user',
            'view_user',
            'manage_roles',
            'create_timesheet',
            'view_timesheet',
            'update_timesheet',
            'submit_timesheet',
            'view_party',
            'view_project',
            'view_reports',
            'create_user_details',
            'view_user_details',
            'update_user_details',
            'delete_user_details',
            'create_internal_user',
            'view_internal_user',
            'update_internal_user',
            'delete_internal_user',
            'status_update_internal_user',
        ]);

        // User - Basic permissions
        $userRole->syncPermissions([
            'create_timesheet',
            'view_timesheet',
            'update_timesheet',
            'submit_timesheet',
            'view_party',
            'view_project',
        ]);

        // Create Users
        $systemAdmin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@system.com',
            'username' => 'systemadmin',
            'password' => Hash::make('password123'),
            'phone' => '+1111111111',
            'gender' => 'male',
            'marital_status' => 'single',
            'business_id' => null, // System admin has no business
            'status' => 'approved',
        ]);
        $systemAdmin->assignRole('System Admin');

        $businessAdmin = User::create([
            'name' => 'John Doe',
            'email' => 'john@demotech.com',
            'username' => 'johndoe',
            'password' => Hash::make('password123'),
            'phone' => '+1222222222',
            'gender' => 'male',
            'marital_status' => 'married',
            'business_id' => $business->id,
            'status' => 'approved',
        ]);
        $businessAdmin->assignRole('Business Admin');

        // Update business owner
        $business->update(['owner_id' => $businessAdmin->id]);

        $staff = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@demotech.com',
            'username' => 'janesmith',
            'password' => Hash::make('password123'),
            'phone' => '+1333333333',
            'gender' => 'female',
            'marital_status' => 'single',
            'business_id' => $business->id,
            'status' => 'approved',
        ]);
        $staff->assignRole('Staff');

        $regularUser = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@demotech.com',
            'username' => 'bobjohnson',
            'password' => Hash::make('password123'),
            'phone' => '+1444444444',
            'gender' => 'male',
            'marital_status' => 'married',
            'business_id' => $business->id,
            'status' => 'approved',
        ]);
        $regularUser->assignRole('User');

        // Create Parties (Clients)
        $client1 = Party::create([
            'business_id' => $business->id,
            'name' => 'ABC Corporation',
            'phone' => '+1555555555',
            'zip_code' => '10001',
            'address' => '456 Business Ave, New York, NY',
            'party_type' => 'client',
        ]);

        $client2 = Party::create([
            'business_id' => $business->id,
            'name' => 'XYZ Industries',
            'phone' => '+1666666666',
            'zip_code' => '90001',
            'address' => '789 Industry Blvd, Los Angeles, CA',
            'party_type' => 'client',
        ]);

        // Create Projects
        $project1 = Project::create([
            'business_id' => $business->id,
            'client_id' => $client1->id,
            'name' => 'Website Redesign',
            'code' => 'WEB-001',
        ]);

        $project2 = Project::create([
            'business_id' => $business->id,
            'client_id' => $client2->id,
            'name' => 'Mobile App Development',
            'code' => 'MOB-001',
        ]);

        // ================================
        // Create Internal Users
        // ================================
        $accountManager = InternalUser::create([
            'business_id' => $business->id,
            'name' => 'Alice Account',
            'email' => 'am@demotech.com',
            'phone' => '+1999000001',
            'role' => 'ac_manager',
            'rate' => 10, // 10%
            'commission_on' => 'gross-margin',
            'rate_type' => 'percentage',
            'recuesive' => false,
        ]);

        $bdManager = InternalUser::create([
            'business_id' => $business->id,
            'name' => 'Brian BDM',
            'email' => 'bdm@demotech.com',
            'phone' => '+1999000002',
            'role' => 'bd_manager',
            'rate' => 5, // 5%
            'commission_on' => 'gross-margin',
            'rate_type' => 'percentage',
            'recuesive' => false,
        ]);

        $recruiter = InternalUser::create([
            'business_id' => $business->id,
            'name' => 'Rachel Recruiter',
            'email' => 'recruiter@demotech.com',
            'phone' => '+1999000003',
            'role' => 'recruiter',
            'rate' => 8, // 8%
            'commission_on' => 'net-margin',
            'rate_type' => 'percentage',
            'recuesive' => false,
        ]);

        // ================================
        // Create User Detail for Staff User
        // ================================
        $userDetailStaff = UserDetail::create([
            'business_id' => $business->id,
            'user_id' => $staff->id,

            'party_id' => $client1->id, // client assignment

            // Rates
            'client_rate' => 100,        // client pays
            'consultant_rate' => 60,     // staff gets

            // Account Manager Commission
            'account_manager_id' => $accountManager->id,
            'account_manager_commission' => 10,
            'account_manager_commission_rate_type' => 1,
            'account_manager_recurssive' => false,

            // Business Development Manager Commission
            'business_development_manager_id' => $bdManager->id,
            'business_development_manager_commission' => 5,
            'business_development_manager_commission_rate_type' => 1,
            'business_development_manager_recurssive' => false,

            // Recruiter Commission
            'recruiter_id' => $recruiter->id,
            'recruiter_commission' => 8,
            'recruiter_rate_type' => 1,
            'recruiter_recurssive' => false,

            // Contract
            'start_date' => now()->subMonth(),
            'active' => true,
        ]);

        // Create Timesheet Defaults
        TimesheetDefault::create([
            'business_id' => $business->id,
            'user_id' => null, // Business-wide default
            'default_daily_hours' => 8.00,
            'default_extra_hours' => 0.00,
            'default_vacation_hours' => 0.00,
        ]);

        // Create Sample Timesheets
        $timesheet1 = Timesheet::create([
            'business_id' => $business->id,
            'user_id' => $staff->id,
            'user_detail_id' => $userDetailStaff->id,
            'client_id' => $client1->id,
            'start_date' => now()->startOfWeek(),
            'end_date' => now()->endOfWeek(),
            'status' => 'submitted',
            'total_hours' => 40.00,
            'remarks' => 'Weekly timesheet for Website Redesign project',
            'submitted_at' => now(),
        ]);

        // Create entries for timesheet1
        for ($i = 0; $i < 5; $i++) {
            TimesheetEntry::create([
                'business_id' => $business->id,
                'timesheet_id' => $timesheet1->id,
                'entry_date' => now()->startOfWeek()->addDays($i),
                'daily_hours' => 8.00,
                'extra_hours' => 0.00,
                'vacation_hours' => 0.00,
                'note' => 'Worked on frontend development',
            ]);
        }

        $timesheet2 = Timesheet::create([
            'business_id' => $business->id,
            'user_id' => $regularUser->id,
            'user_detail_id' => $userDetailStaff->id,
            'client_id' => $client2->id,
            'start_date' => now()->subWeek()->startOfWeek(),
            'end_date' => now()->subWeek()->endOfWeek(),
            'status' => 'approved',
            'total_hours' => 42.00,
            'remarks' => 'Mobile app development - Sprint 1',
            'submitted_at' => now()->subWeek(),
            'approved_at' => now()->subWeek()->addDay(),
            'approved_by' => $businessAdmin->id,
        ]);

        // Create entries for timesheet2
        for ($i = 0; $i < 5; $i++) {
            TimesheetEntry::create([
                'business_id' => $business->id,
                'timesheet_id' => $timesheet2->id,
                'entry_date' => now()->subWeek()->startOfWeek()->addDays($i),
                'daily_hours' => 8.00,
                'extra_hours' => $i === 2 ? 2.00 : 0.00, // Overtime on Wednesday
                'vacation_hours' => 0.00,
                'note' => $i === 2 ? 'Worked overtime for urgent bug fix' : 'Regular development work',
            ]);
        }

        // Create Holidays
        $holidays = [
            [
                'business_id' => $business->id,
                'holiday_date' => now()->year . '-01-01',
                'description' => 'New Year\'s Day - New Year celebration',
                'recurring' => true,
            ],
            [
                'business_id' => $business->id,
                'holiday_date' => now()->year . '-07-04',
                'description' => 'Independence Day - Independence Day celebration',
                'recurring' => true,
            ],
            [
                'business_id' => $business->id,
                'holiday_date' => now()->year . '-09-01',
                'description' => 'Labor Day - Labor Day holiday',
                'recurring' => true,
            ],
            [
                'business_id' => $business->id,
                'holiday_date' => now()->year . '-11-28',
                'description' => 'Thanksgiving - Thanksgiving holiday',
                'recurring' => true,
            ],
            [
                'business_id' => $business->id,
                'holiday_date' => now()->year . '-12-25',
                'description' => 'Christmas - Christmas celebration',
                'recurring' => true,
            ],
            [
                'business_id' => $business->id,
                'holiday_date' => now()->year . '-03-15',
                'description' => 'Company Anniversary - Company founding anniversary',
                'recurring' => true,
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('-----------------------------------');
        $this->command->info('System Admin:');
        $this->command->info('  Email: admin@system.com');
        $this->command->info('  Password: password123');
        $this->command->info('');
        $this->command->info('Business Admin:');
        $this->command->info('  Email: john@demotech.com');
        $this->command->info('  Password: password123');
        $this->command->info('');
        $this->command->info('Staff:');
        $this->command->info('  Email: jane@demotech.com');
        $this->command->info('  Password: password123');
        $this->command->info('');
        $this->command->info('User:');
        $this->command->info('  Email: bob@demotech.com');
        $this->command->info('  Password: password123');
        $this->command->info('-----------------------------------');
    }
}
