<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessPermission;
use App\Models\EmailTemplate;
use App\Models\User;
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
        // Create a Demo Business
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

            // Internal User Management
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

            // Email Template Management
            'create_email_template',
            'view_email_template',
            'update_email_template',
            'delete_email_template',
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
            'status_update_user',
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
            'create_email_template',
            'view_email_template',
            'update_email_template',
            'delete_email_template',
        ]);

        // Staff - Limited permissions
        $staffRole->syncPermissions([
            'create_user',
            'view_user',
            'manage_roles',
            'create_timesheet',
            'view_timesheet',
            'update_timesheet',
            'approve_timesheet',
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
            'create_email_template',
            'view_email_template',
        ]);

        // User - Basic permissions
        $userRole->syncPermissions([
            'create_timesheet',
            'view_timesheet',
            'update_timesheet',
            'submit_timesheet',
            'view_party',
            'view_project',
            'view_email_template',
        ]);

        // ================================
        // Create Users
        // ================================

        // 1. System Admin (no business)
        $systemAdmin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@system.com',
            'username' => 'systemadmin',
            'password' => Hash::make('password123'),
            'phone' => '+1111111111',
            'gender' => 'male',
            'marital_status' => 'single',
            'business_id' => null,
            'status' => 'approved',
        ]);
        $systemAdmin->assignRole('System Admin');

        // 2. Business Admin
        $businessAdmin = User::create([
            'name' => 'Business Admin',
            'email' => 'admin@business.com',
            'username' => 'businessadmin',
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

        // 3. Staff / Supervisor
        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@business.com',
            'username' => 'staffuser',
            'password' => Hash::make('password123'),
            'phone' => '+1333333333',
            'gender' => 'female',
            'marital_status' => 'single',
            'business_id' => $business->id,
            'status' => 'approved',
        ]);
        $staff->assignRole('Staff');

        // 4. Regular User
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@business.com',
            'username' => 'regularuser',
            'password' => Hash::make('password123'),
            'phone' => '+1444444444',
            'gender' => 'male',
            'marital_status' => 'single',
            'business_id' => $business->id,
            'status' => 'approved',
        ]);
        $regularUser->assignRole('User');

        // ================================
        // Create 6 Default Email Templates (business_id = null)
        // ================================

        // 1. Submit Template
        $submitTemplate = EmailTemplate::create([
            'business_id' => null,
            'template_name' => 'Timesheet submit, {start_date} To {end_date}, {client_name}',
            'subject' => 'Timesheet submit, {start_date} To {end_date}, {client_name}',
            'body' => "Hello,\n\nTimesheet is submit for client : {client_name}\n\nfor time period: {start_date} To {end_date}\n\nThank you.",
            'template_type' => 'general',
        ]);

        // 2. Resubmit Template
        $resubmitTemplate = EmailTemplate::create([
            'business_id' => null,
            'template_name' => 'Timesheet resubmit, {start_date} To {end_date}, {client_name}',
            'subject' => 'Timesheet resubmit, {start_date} To {end_date}, {client_name}',
            'body' => "Hello,\n\nTimesheet is submit for client : {client_name}\n\nfor time period: {start_date} To {end_date}\n\nThank you.",
            'template_type' => 'timesheet_submit',
        ]);

        // 3. Approve Template
        $approveTemplate = EmailTemplate::create([
            'business_id' => null,
            'template_name' => 'Timesheet approve, {start_date} To {end_date}, {user_name} for client: {client_name}',
            'subject' => 'Timesheet Approved: {start_date} to {end_date} - {user_name}',
            'body' => "Hello,\n\n" .
                     "Your timesheet has been approved.\n\n" .
                     "Consultant: {user_name}\n" .
                     "Client: {client_name}\n" .
                     "Period: {start_date} to {end_date}\n\n" .
                     "--- TIMESHEET DETAILS ---\n" .
                     "{timesheet_table}\n\n" .
                     "--- FINANCIAL SUMMARY ---\n" .
                     "Total Hours: {total_hours}\n" .
                     "Total Bill Amount: {total_bill_amount}\n" .
                     "Total Pay Amount: {total_pay_amount}\n" .
                     "Gross Margin: {gross_margin}\n" .
                     "Net Margin: {net_margin}%\n\n" .
                     "Thank you.",
            'template_type' => 'timesheet_approve',
        ]);

        // 4. Reject Template
        $rejectTemplate = EmailTemplate::create([
            'business_id' => null,
            'template_name' => 'Timesheet reject, {start_date} To {end_date}, {user_name} for client: {client_name}',
            'subject' => 'Timesheet reject, {start_date} To {end_date}, {user_name} for client: {client_name}',
            'body' => "Hello,\n\nTimesheet is reject of {user_name} for client : {client_name}\n\nfor time period: {start_date} To {end_date}\n\nPlease check\n\nThank you.",
            'template_type' => 'timesheet_reject',
        ]);

        // 5. Pending Template
        $pendingTemplate = EmailTemplate::create([
            'business_id' => null,
            'template_name' => 'Timesheet pending, {start_date} To {end_date}, {user_name} for client: {client_name}',
            'subject' => 'Timesheet pending, {start_date} To {end_date}, {user_name} for client: {client_name}',
            'body' => "Hello,\n\nYour timesheet is pending for client : {client_name}\n\nfor time period: {start_date} To {end_date}\n\nPlease check\n\nThank you.",
            'template_type' => 'pending_timesheet_reminder',
        ]);

        // 6. Request Access Template
        $requestAccessTemplate = EmailTemplate::create([
            'business_id' => null,
            'template_name' => 'Access Request',
            'subject' => 'Request for access plan',
            'body' => "please add on following plan,\n\nAlso send us invoice to this add on plan,\n\nThank you",
            'template_type' => 'general',
        ]);

        // ================================
        // Create Template-Role Relationships
        // ================================

        // All roles can use all templates
        $allTemplates = [
            $submitTemplate,
            $resubmitTemplate,
            $approveTemplate,
            $rejectTemplate,
            $pendingTemplate,
            $requestAccessTemplate,
        ];

        $allRoles = [
            $systemAdminRole,
            $businessAdminRole,
            $staffRole,
            $userRole,
        ];

        // Create relationships in email_template_used_bies
        foreach ($allTemplates as $template) {
            foreach ($allRoles as $role) {
                \DB::table('email_template_used_bies')->insert([
                    'mail_template_id' => $template->id,
                    'role_id' => $role->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('Login Credentials:');
        $this->command->info('===========================================');
        $this->command->info('');
        $this->command->info('1. System Admin:');
        $this->command->info('   Email: admin@system.com');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('2. Business Admin:');
        $this->command->info('   Email: admin@business.com');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('3. Staff / Supervisor:');
        $this->command->info('   Email: staff@business.com');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('4. Regular User:');
        $this->command->info('   Email: user@business.com');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('6 Default Email Templates Created');
        $this->command->info('===========================================');
    }
}
