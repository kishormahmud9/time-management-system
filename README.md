# 📋 Timesheet Management System

[![Laravel](https://img.shields.io/badge/Laravel-11.31-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A comprehensive **multi-tenant timesheet management system** built with Laravel 11, designed for businesses to manage employee working hours, projects, clients, and timesheet approvals.

---

## 📌 Table of Contents

- [Project Overview](#-project-overview)
- [Key Features](#-key-features)
- [Technology Stack](#-technology-stack)
- [Installation & Setup](#-installation--setup)
- [Project Structure](#-project-structure)
- [API Endpoints](#-api-endpoints)
- [Database Schema](#-database-schema)
- [Authentication & Authorization](#-authentication--authorization)
- [Developer Guide](#-developer-guide)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [License](#-license)

---

## 📌 Project Overview

The **Timesheet Management System** is a robust, multi-tenant SaaS application that enables businesses to:

- 📊 Track employee working hours (daily, weekly, monthly)
- 👥 Manage multiple businesses with data isolation
- 📝 Create and manage projects and clients
- ✅ Implement approval workflows for timesheets
- 📧 Send email notifications
- 📈 Generate reports (PDF, Excel)
- 🔐 Role-based access control (RBAC)

### Use Cases

- **HR Departments:** Track employee hours and manage payroll
- **Project Managers:** Monitor project time allocation
- **Consulting Firms:** Bill clients based on timesheet entries
- **Remote Teams:** Track distributed team working hours
- **Freelancers:** Manage multiple client projects

---

## 📌 Key Features

### 🔐 Authentication & Security

- ✅ JWT-based authentication
- ✅ OTP-based password reset
- ✅ Role-based access control (4 roles)
- ✅ Permission management system
- ✅ Activity logging (IP, User Agent)
- ✅ Secure password hashing

### 🏢 Multi-Tenancy

- ✅ Business-level data isolation
- ✅ Business-specific users, projects, clients
- ✅ Business-specific holidays and defaults
- ✅ Business owner registration workflow

### ⏰ Timesheet Management

- ✅ Create, read, update, delete timesheets
- ✅ Daily time entries (regular, extra, vacation hours)
- ✅ Timesheet status workflow (draft → submitted → approved/rejected)
- ✅ Attachment support (PDF, DOC, images)
- ✅ Total hours auto-calculation
- ✅ Holiday validation

### 👥 User Management

- ✅ User CRUD operations
- ✅ Profile management
- ✅ User status workflow (approved/pending/rejected)
- ✅ Role assignment
- ✅ Activity logging

### 📊 Project & Client Management

- ✅ Project CRUD operations
- ✅ Party management (clients, vendors, employees)
- ✅ Business-specific filtering
- ✅ Project-client relationships

### 📧 Email System

- ✅ Custom email templates
- ✅ Welcome emails
- ✅ OTP emails
- ✅ Timesheet notifications
- ✅ Password reset success emails

### 📈 Reporting & Analytics

- ✅ PDF report generation
- ✅ Excel export functionality
- ✅ Dashboard statistics
- ✅ Monthly overview
- ✅ Timesheet status analytics

---

## 📌 Technology Stack

### Backend

- **Framework:** Laravel 11.31
- **PHP Version:** 8.2+
- **Database:** SQLite (default), MySQL/PostgreSQL supported
- **Authentication:** JWT (tymon/jwt-auth ^2.2)
- **Authorization:** Spatie Laravel Permission (^6.21)

### Additional Packages

- **PDF Generation:** barryvdh/laravel-dompdf (^3.1)
- **Excel Export:** maatwebsite/excel (^1.1)
- **File Storage:** Local filesystem (configurable)

### Development Tools

- **Code Style:** Laravel Pint (^1.13)
- **Testing:** PHPUnit (^11.0.1)
- **Docker:** Laravel Sail (^1.26)
- **Logging:** Laravel Pail (^1.1)

---

## 📌 Installation & Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM (for frontend assets)
- SQLite (default) or MySQL/PostgreSQL

### Step 1: Clone the Repository

```bash
git clone https://github.com/your-username/timesheet-management-system.git
cd timesheet-management-system/backend
```

### Step 2: Install Dependencies

```bash
composer install
npm install
```

### Step 3: Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### Step 4: Configure Environment Variables

Edit `.env` file:

```env
APP_NAME="Timesheet Management System"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# Or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=timesheet_db
# DB_USERNAME=root
# DB_PASSWORD=

JWT_SECRET=your-jwt-secret-key

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 5: Database Setup

```bash
# Create SQLite database (if using SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### Step 6: Storage Setup

```bash
php artisan storage:link
```

### Step 7: Start Development Server

```bash
# Option 1: Using Laravel's built-in server
php artisan serve

# Option 2: Using Laravel Sail (Docker)
./vendor/bin/sail up

# Option 3: Using the dev script (includes queue, logs, vite)
composer dev
```

The API will be available at `http://localhost:8000`

---

## 📌 Project Structure

```
backend/
├── app/
│   ├── Exceptions/              # Exception handlers
│   ├── Exports/                 # Excel export classes
│   ├── Helpers/                 # Helper functions
│   │   └── helpers.php
│   ├── Http/
│   │   ├── Controllers/         # API Controllers
│   │   │   ├── AuthController.php
│   │   │   ├── Company/
│   │   │   │   └── BusinessController.php
│   │   │   ├── Party/
│   │   │   │   └── PartyController.php
│   │   │   ├── Profile/
│   │   │   │   └── ProfileController.php
│   │   │   ├── RoleAndPermission/
│   │   │   │   ├── RoleController.php
│   │   │   │   ├── PermissionController.php
│   │   │   │   ├── RoleHasPermissionController.php
│   │   │   │   └── UserHasRoleController.php
│   │   │   ├── Timesheet/
│   │   │   │   └── TimesheetManageController.php
│   │   │   ├── User/
│   │   │   │   ├── UserManageController.php
│   │   │   │   └── UserActivityLogController.php
│   │   │   ├── Mail/
│   │   │   │   └── EmailTemplateController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── HolidayController.php
│   │   │   ├── AttachmentController.php
│   │   │   ├── DashboardController.php
│   │   │   └── ReportController.php
│   │   └── Middleware/          # Custom middleware
│   ├── Mail/                    # Mailable classes
│   │   ├── WelcomeEmail.php
│   │   ├── OTPEmail.php
│   │   ├── PasswordResetSuccessEmail.php
│   │   └── TimesheetSubmittedEmail.php
│   ├── Models/                  # Eloquent models
│   │   ├── User.php
│   │   ├── Business.php
│   │   ├── Timesheet.php
│   │   ├── TimesheetEntry.php
│   │   ├── Project.php
│   │   ├── Party.php
│   │   ├── Holiday.php
│   │   └── ...
│   ├── Notifications/           # Notification classes
│   │   ├── TimesheetSubmitted.php
│   │   └── TimesheetStatusUpdated.php
│   ├── Providers/               # Service providers
│   ├── Services/                # Business logic services
│   │   ├── BusinessRegistrationService.php
│   │   ├── RoleService.php
│   │   ├── SlugService.php
│   │   └── UserAccessService.php
│   └── Traits/                  # Reusable traits
│       └── UserActivityTrait.php
├── bootstrap/
├── config/                      # Configuration files
├── database/
│   ├── factories/               # Model factories
│   ├── migrations/              # Database migrations
│   └── seeders/                  # Database seeders
├── public/                      # Public assets
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   ├── api.php                  # API routes
│   └── web.php                  # Web routes
├── storage/                     # Storage directory
├── tests/                       # Test files
├── vendor/                      # Composer dependencies
├── .env.example                 # Environment example
├── composer.json                # PHP dependencies
├── package.json                 # Node dependencies
└── README.md                    # This file
```

---

## 📌 API Endpoints

### Authentication Endpoints

#### Public Routes (No Authentication Required)

| Method | Endpoint                | Description                |
| ------ | ----------------------- | -------------------------- |
| POST   | `/api/register`         | Register business owner    |
| POST   | `/api/login`            | User login                 |
| POST   | `/api/forget-password`  | Request password reset OTP |
| POST   | `/api/otp-varification` | Verify OTP                 |
| POST   | `/api/reset-password`   | Reset password             |

#### Protected Routes (JWT Token Required)

| Method | Endpoint               | Description       | Roles |
| ------ | ---------------------- | ----------------- | ----- |
| POST   | `/api/logout`          | Logout user       | All   |
| POST   | `/api/refresh`         | Refresh JWT token | All   |
| GET    | `/api/profile`         | Get user profile  | All   |
| POST   | `/api/profile-edit`    | Update profile    | All   |
| POST   | `/api/change-password` | Change password   | All   |
| POST   | `/api/company-update`  | Update company    | Admin |
| GET    | `/api/company`         | View company      | All   |
| POST   | `/api/update-weekend`  | Update weekend    | Admin |

### Business Management (System Admin Only)

| Method | Endpoint             | Description            |
| ------ | -------------------- | ---------------------- |
| POST   | `/api/business`      | Create business        |
| GET    | `/api/business`      | List all businesses    |
| GET    | `/api/business/{id}` | Get business details   |
| POST   | `/api/business/{id}` | Update business        |
| DELETE | `/api/business/{id}` | Delete business        |
| PATCH  | `/api/business/{id}` | Update business status |

### User Management (System Admin & Business Admin)

| Method | Endpoint                 | Description           |
| ------ | ------------------------ | --------------------- |
| POST   | `/api/user`              | Create user           |
| GET    | `/api/users`             | List users            |
| GET    | `/api/user/{id}`         | Get user details      |
| POST   | `/api/user/{id}`         | Update user           |
| DELETE | `/api/user/{id}`         | Delete user           |
| PATCH  | `/api/user/{id}`         | Update user status    |
| POST   | `/api/internaluser`      | Create internal user  |
| GET    | `/api/internalusers`     | List internal users   |
| GET    | `/api/internaluser/{id}` | Internal user details |
| POST   | `/api/internaluser/{id}` | Update internal user  |
| DELETE | `/api/internaluser/{id}` | Delete internal user  |
| PATCH  | `/api/internaluser/{id}` | Update internal role  |
| POST   | `/api/user-details`      | Set user details      |
| GET    | `/api/user-details`      | List user details     |
| GET    | `/api/user-details/{id}` | View user details     |
| POST   | `/api/user-details/{id}` | Update user details   |
| DELETE | `/api/user-details/{id}` | Delete user details   |

### Timesheet Management (All Authenticated Users)

| Method | Endpoint                            | Description             |
| ------ | ----------------------------------- | ----------------------- |
| POST   | `/api/timesheet`                    | Create timesheet        |
| GET    | `/api/timesheet`                    | List timesheets         |
| GET    | `/api/timesheet/{id}`               | Get timesheet details   |
| PUT    | `/api/timesheet/{id}`               | Update timesheet        |
| DELETE | `/api/timesheet/{id}`               | Delete timesheet        |
| PATCH  | `/api/timesheet/{id}`               | Update timesheet status |
| GET    | `/api/timesheet-defaults`           | Get timesheet defaults  |
| GET    | `/api/user/{id}/timesheet-defaults` | User specific defaults  |
| GET    | `/api/scheduler`                    | Schedule overview       |
| GET    | `/api/attachment/{id}/download`     | Download attachment     |

### Project Management

| Method | Endpoint             | Description         | Roles |
| ------ | -------------------- | ------------------- | ----- |
| GET    | `/api/projects`      | List projects       | All   |
| GET    | `/api/projects/{id}` | Get project details | All   |
| POST   | `/api/projects`      | Create project      | Admin |
| POST   | `/api/projects/{id}` | Update project      | Admin |
| DELETE | `/api/projects/{id}` | Delete project      | Admin |

### Party Management (Clients, Vendors, Employees)

| Method | Endpoint          | Description         |
| ------ | ----------------- | ------------------- |
| POST   | `/api/party`      | Create party        |
| GET    | `/api/parties`    | List all parties    |
| GET    | `/api/clients`    | List clients only   |
| GET    | `/api/vendors`    | List vendors only   |
| GET    | `/api/employees`  | List employees only |
| GET    | `/api/party/{id}` | Get party details   |
| PUT    | `/api/party/{id}` | Update party        |
| DELETE | `/api/party/{id}` | Delete party        |

### Role & Permission Management

| Method | Endpoint                                | Description                | Roles        |
| ------ | --------------------------------------- | -------------------------- | ------------ |
| POST   | `/api/role`                             | Create role                | System Admin |
| GET    | `/api/roles`                            | List roles                 | All          |
| GET    | `/api/role/{id}`                        | Get role details           | All          |
| POST   | `/api/role/{id}`                        | Update role                | System Admin |
| DELETE | `/api/role/{id}`                        | Delete role                | System Admin |
| POST   | `/api/permission`                       | Create permission          | System Admin |
| GET    | `/api/permissions`                      | List permissions           | All          |
| POST   | `/api/role-has-permission`              | Assign permissions to role | Admin        |
| POST   | `/api/user-has-role`                    | Assign role to user        | Admin        |
| GET    | `/api/user-permissions`                 | User permissions           | All          |
| GET    | `/api/supervisor-permissions`           | Supervisor permissions     | All          |
| GET    | `/api/supervisor-available-permissions` | Avail. supervisor perms    | Admin        |
| GET    | `/api/user-available-permissions`       | Avail. user perms          | Admin        |
| GET    | `/api/permission/{id}`                  | Permission details         | All          |

### Other Endpoints

| Method | Endpoint                         | Description                  |
| ------ | -------------------------------- | ---------------------------- |
| GET    | `/api/dashboard`                 | Get dashboard statistics     |
| GET    | `/api/reports`                   | Generate reports (PDF/Excel) |
| GET    | `/api/holidays`                  | List holidays                |
| POST   | `/api/attachments`               | Upload attachment            |
| GET    | `/api/manage-activity`           | Get activity logs            |
| GET    | `/api/chart/summary`             | Chart summary analytics      |
| GET    | `/api/chart/trend`               | Chart trend analytics        |
| GET    | `/api/revenue/dashboard-data`    | Revenue analytics            |
| GET    | `/api/consultant/dashboard-data` | Consultant stats             |
| GET    | `/api/hours/dashboard-data`      | Hours distribution           |
| GET    | `/api/user-dashboard-data`       | User stats dashboard         |
| GET    | `/api/system-dashboard`          | Platform global dashboard    |
| GET    | `/api/staff-dashboard`           | Staff operation dashboard    |
| GET    | `/api/supervisor-dashboard-data` | Supervisor analytics         |
| GET    | `/api/email-template`            | List email templates         |
| GET    | `/api/email-template/{id}`       | Email template details       |
| POST   | `/api/email-template`            | Create email template        |
| PUT    | `/api/email-template/{id}`       | Update email template        |
| DELETE | `/api/email-template/{id}`       | Delete email template        |
| POST   | `/api/holiday`                   | Create business holiday      |
| POST   | `/api/holiday/{id}`              | Update business holiday      |
| DELETE | `/api/holiday/{id}`              | Delete business holiday      |

---

## 📌 Database Schema

### Core Tables

#### Users

- `id`, `name`, `username`, `email`, `password`
- `phone`, `gender`, `marital_status`
- `business_id`, `status` (approved/pending/rejected)
- `image`, `signature`
- `created_at`, `updated_at`

#### Businesses

- `id`, `name`, `slug`, `email`, `phone`, `address`
- `owner_id`, `logo`, `status` (active/inactive/pending)
- `created_at`, `updated_at`

#### Timesheets

- `id`, `business_id`, `user_id`, `client_id`, `project_id`
- `start_date`, `end_date`, `status` (draft/submitted/approved/rejected)
- `total_hours`, `remarks`, `attachment_path`
- `approved_by`, `submitted_at`, `approved_at`
- `created_at`, `updated_at`

#### Timesheet Entries

- `id`, `business_id`, `timesheet_id`, `entry_date`
- `daily_hours`, `extra_hours`, `vacation_hours`, `note`
- `created_at`, `updated_at`

#### Projects

- `id`, `business_id`, `client_id`, `name`, `code`
- `created_at`, `updated_at`

#### Parties (Clients/Vendors/Employees)

- `id`, `business_id`, `type` (client/vendor/employee)
- `name`, `email`, `phone`, `address`
- `created_at`, `updated_at`

### Relationship Diagram

```
Users ──┬──> Businesses (owner)
        └──> Timesheets
              └──> TimesheetEntries

Businesses ──┬──> Users
             ├──> Projects
             ├──> Parties
             └──> Holidays

Projects ──> Parties (client)
Timesheets ──> Projects
Timesheets ──> Parties (client)
```

---

## 📌 Authentication & Authorization

### Authentication Flow

1. **Registration:** Business owner registers → Status: `pending`
2. **Admin Approval:** System Admin approves → Status: `approved`
3. **Login:** User logs in with email/password → Receives JWT token
4. **Token Usage:** Include token in `Authorization: Bearer {token}` header

### JWT Token Structure

```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "role": "Business Admin"
}
```

### Roles & Permissions

#### System Admin

- Full access to all businesses
- Can create/manage businesses
- Can assign any role
- Can manage permissions

#### Business Admin

- Full access to own business data
- Can create/manage users in own business
- Can assign roles: User, Staff, Business Admin
- Can approve/reject timesheets

#### Staff

- Can create/view own timesheets
- Can view projects and clients
- Cannot approve timesheets

#### User

- Can create/view own timesheets
- Can view own profile
- Cannot approve timesheets

### Permission System

Uses **Spatie Laravel Permission** package:

- Roles: System Admin, Business Admin, Staff, User
- Permissions: Granular permissions (e.g., `create-timesheet`, `approve-timesheet`)
- Role-Permission mapping: Flexible assignment

---

## 📌 Developer Guide

### Code Style

The project uses **Laravel Pint** for code formatting:

```bash
# Format code
./vendor/bin/pint

# Check code style
./vendor/bin/pint --test
```

### Adding New Features

1. **Create Migration**

    ```bash
    php artisan make:migration create_example_table
    ```

2. **Create Model**

    ```bash
    php artisan make:model Example
    ```

3. **Create Controller**

    ```bash
    php artisan make:controller ExampleController
    ```

4. **Create Service (if needed)**

    ```bash
    # Manual creation in app/Services/
    ```

5. **Add Routes**
    ```php
    // routes/api.php
    Route::get('/examples', [ExampleController::class, 'index']);
    ```

### Database Migrations

```bash
# Create migration
php artisan make:migration create_example_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Refresh migrations
php artisan migrate:fresh

# Seed database
php artisan db:seed
```

### Creating Services

Services should contain business logic:

```php
// app/Services/ExampleService.php
namespace App\Services;

class ExampleService
{
    public function doSomething(): void
    {
        // Business logic here
    }
}
```

### Using Traits

```php
// app/Traits/UserActivityTrait.php
trait UserActivityTrait
{
    public function logActivity(string $action): void
    {
        // Logging logic
    }
}

// In Controller
use UserActivityTrait;

public function store(Request $request)
{
    $this->logActivity('create_resource');
}
```

### Error Handling

```php
try {
    // Operation
} catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'message' => 'Operation failed',
        'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
    ], 500);
}
```

### Validation

```php
$validator = Validator::make($request->all(), [
    'name' => 'required|string|max:100',
    'email' => 'required|email|unique:users,email',
]);

if ($validator->fails()) {
    return response()->json([
        'success' => false,
        'errors' => $validator->errors()
    ], 422);
}
```

---

## 📌 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter ExampleTest

# Run with coverage
php artisan test --coverage
```

### Writing Tests

```php
// tests/Feature/ExampleTest.php
namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_example(): void
    {
        $response = $this->get('/api/example');
        $response->assertStatus(200);
    }
}
```

---

## 📌 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate application key: `php artisan key:generate`
- [ ] Generate JWT secret: `php artisan jwt:secret`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Optimize: `php artisan config:cache`, `php artisan route:cache`
- [ ] Set up queue worker: `php artisan queue:work`
- [ ] Configure web server (Nginx/Apache)
- [ ] Set up SSL certificate
- [ ] Configure file storage (S3 recommended)
- [ ] Set up monitoring and logging

### Environment Variables for Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

JWT_SECRET=your-production-jwt-secret

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

---

## 📌 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- Use Laravel Pint for formatting
- Write tests for new features
- Update documentation
- Follow semantic versioning

---

## 📌 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 📌 Support

For support, email support@example.com or create an issue in the repository.

---

## 📌 Acknowledgments

- Laravel Framework
- Spatie Laravel Permission
- Tymon JWT Auth
- All contributors

---

**Built with ❤️ using Laravel**
