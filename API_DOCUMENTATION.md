# 📘 Timesheet Management System - API Documentation

## 1. Overview

**System Description:**
The Timesheet Management System is a multi-tenant backend API built with Laravel 11. It facilitates timesheet tracking, project management, and reporting for multiple businesses with strict data isolation.

**Authentication:**
The API uses **JWT (JSON Web Token)** for authentication.
All protected endpoints require the following header:

```http
Authorization: Bearer <your_token_here>
Accept: application/json
```

**Multi-Tenancy:**

- Data is filtered by `business_id` (the organizational context).
- **System Admins** have global access (manage businesses, global roles).
- **Business Admins** manage their specific organization (users, clients, holidays).
- **Staff** manage operational data (timesheet approvals, user details).
- **Users** track their own work (create/view timesheets).

---

## 2. Public APIs (No Auth)

### Authentication Module

- **POST `/api/register`**: Register a new business owner.
- **POST `/api/login`**: Login and receive JWT token + user info.
- **POST `/api/logout`**: Terminate session and invalidate token.
- **POST `/api/refresh`**: Refresh the JWT token.
- **POST `/api/forget-password`**: Request OTP for password reset.
- **POST `/api/otp-varification`**: Verify the 6-digit OTP.
- **POST `/api/reset-password`**: Reset password using verified email.

---

## 3. Core APIs (All Authenticated Roles)

### Profile & Settings

- **GET `/api/profile`**: View current user profile info.
- **POST `/api/profile-edit`**: Update personal information (name, phone, address, gender, marital status, image, signature).
- **POST `/api/change-password`**: Update account password.
- **GET `/api/company`**: View business information.
- **POST `/api/company-update`**: Update business details (Business Admin only recommended).
- **POST `/api/update-weekend`**: Set weekend days for the business.

### Timesheet Operations

- **POST `/api/timesheet`**: Create a new timesheet.
- **GET `/api/timesheet`**: List timesheets (filtered by business).
- **GET `/api/timesheet/{id}`**: View specific timesheet details.
- **GET `/api/timesheet-defaults`**: Get default hours (daily, extra, vacation).
- **GET `/api/user/{id}/timesheet-defaults`**: Get default settings for a specific user.
- **GET `/api/attachment/{id}/download`**: Download timesheet attachments.
- **GET `/api/scheduler`**: Get timesheet entries filtered by date/month for calendar view.

### Dashboards & Analytics

- **GET `/api/chart/summary`**: Get summary stats for charts.
- **GET `/api/chart/trend`**: Get trending data for charts.
- **GET `/api/revenue/dashboard-data`**: Revenue report summary.
- **GET `/api/consultant/dashboard-data`**: Consultant performance statistics.
- **GET `/api/hours/dashboard-data`**: Working hours distribution.
- **GET `/api/user-dashboard-data`**: Personalized dashboard for regular users.

### Informational & Lists

- **GET `/api/parties`**: List all parties (Clients, Vendors, Employees).
- **GET `/api/clients`**: List all clients.
- **GET `/api/vendors`**: List all vendors.
- **GET `/api/employees`**: List all employees.
- **GET `/api/party/{id}`**: View specific party details.
- **GET `/api/holidays`**: List upcoming holidays.
- **GET `/api/holiday/{id}`**: View holiday details.
- **GET `/api/email-template`**: View available email templates.
- **GET `/api/email-template/{id}`**: View specific template details.
- **GET `/api/permissions`**: List all permissions.
- **GET `/api/user-permissions`**: Current user's specific permissions.
- **GET `/api/supervisor-permissions`**: Permissions available for supervisors.
- **GET `/api/supervisor-available-permissions`**: List of permissions available for assignment to supervisors.
- **GET `/api/user-available-permissions`**: List of permissions available for assignment to regular users.
- **GET `/api/permission/{id}`**: View specific permission details.
- **GET `/api/roles`**: List all roles.
- **GET `/api/role/{id}`**: View specific role details.

---

## 4. Administrative APIs (Role-Based)

### 🏢 Business Admin (Full Organization Control)

- **POST `/api/role-has-permission`**: Assign permissions to a role.
- **POST `/api/user-has-role`**: Assign a role to a user.
- **GET `/api/manage-activity`**: View system activity logs for the organization.
- **POST `/api/holiday`**: Create a new holiday.
- **POST `/api/holiday/{id}`**: Update a holiday.
- **DELETE `/api/holiday/{id}`**: Remove a holiday.

### 🛡️ System Admin (Global Platform Control)

- **POST `/api/business`**: Register a new business tenant.
- **GET `/api/business`**: List all registered businesses.
- **GET `/api/business/{id}`**: View business details.
- **POST `/api/business/{id}`**: Update business details.
- **DELETE `/api/business/{id}`**: Delete a business.
- **PATCH `/api/business/{id}`**: Update business status (e.g., active/inactive).
- **GET `/api/system-dashboard`**: Global analytics for the platform.
- **POST `/api/permission`**: Create global permissions.
- **POST `/api/role`**: Create global roles.

---

## 5. Staff & Management APIs (Business Admin | Staff)

### User Management

- **POST `/api/user`**: Create a new user (Internal or external).
- **GET `/api/users`**: List all users in the business.
- **GET `/api/user/{id}`**: View user details.
- **POST `/api/user/{id}`**: Update user information.
- **DELETE `/api/user/{id}`**: Delete a user.
- **PATCH `/api/user/{id}`**: approve/reject/pending status update.

### Internal User Management

- **POST `/api/internaluser`**: Create internal staff users.
- **GET `/api/internalusers`**: List internal users.
- **GET `/api/internaluser/{id}`**: View internal user details.
- **POST `/api/internaluser/{id}`**: Update internal user info.
- **DELETE `/api/internaluser/{id}`**: Remove internal user.
- **PATCH `/api/internaluser/{id}`**: Update role of an internal user.

### Party & Template Management

- **POST `/api/party`**: Create a new party (Client/Vendor/Employee).
- **PUT `/api/party/{id}`**: Update party details.
- **DELETE `/api/party/{id}`**: Delete a party.
- **POST `/api/email-template`**: Create a new email template.
- **PUT `/api/email-template/{id}`**: Update email template.
- **DELETE `/api/email-template/{id}`**: Delete email template.

### Financial & Details

- **POST `/api/user-details`**: Set billing rates, commissions, and contract details for a user.
- **GET `/api/user-details`**: View all user details.
- **POST `/api/user-details/{id}`**: Update rates/commissions.

### Timesheet Management & Approval

- **POST `/api/timesheet/{id}`**: Management update of a timesheet.
- **PATCH `/api/timesheet/{id}`**: Update timesheet status (**approved**, **rejected**, **submitted**).
- **GET `/api/staff-dashboard`**: Overview for staff/managers.
- **GET `/api/supervisor-dashboard-data`**: Specific overview for supervisors.

---

## 6. Permissions Reference

| Permission Name     | Description                                   |
| :------------------ | :-------------------------------------------- |
| `create_user`       | Can create any type of user.                  |
| `view_user`         | Can see user lists and details.               |
| `update_user`       | Can modify user information.                  |
| `delete_user`       | Can remove users (Business Admin restricted). |
| `approve_timesheet` | Can change timesheet status to approved.      |
| `manage_business`   | System Admin level control over tenants.      |

---

## 7. Error Handling

- **401 Unauthorized**: Token is missing or invalid.
- **403 Forbidden**: Authenticated but lacks required role or permission.
- **422 Unprocessable Content**: Validation failed (returns `errors` object).
- **404 Not Found**: Resource doesn't exist.
- **500 Server Error**: Internal system failure.
