# 📘 Timesheet Management System - API Documentation

## 1. Overview

**System Description:**
The Timesheet Management System is a multi-tenant backend API built with Laravel 11. It allows businesses to manage employees, projects, and timesheets with a robust approval workflow. The system supports multiple organizations (tenants) with strict data isolation.

**Authentication:**
The API uses **JWT (JSON Web Token)** for authentication.
All protected endpoints require the following header:
```http
Authorization: Bearer <your_token_here>
Accept: application/json
Content-Type: application/json
```

**Multi-Tenancy:**
- Users are linked to a specific `business_id`.
- Data is automatically filtered so users can only access records belonging to their business.
- **System Admins** have global access.

**Response Format:**
Standard API response structure:
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "errors": { ... } // Only on error
}
```

---

## 2. Authentication Module

### Login
Authenticate a user and receive a JWT token.

- **URL:** `/api/login`
- **Method:** `POST`
- **Auth Required:** No

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "User login successfully",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "admin@example.com",
      "role": "Business Admin"
    }
  }
}
```

### Register (Business Owner)
Register a new business and owner account.

- **URL:** `/api/register`
- **Method:** `POST`
- **Auth Required:** No

**Request Body:**
```json
{
  "business_name": "Acme Corp",
  "name": "Jane Doe",
  "email": "jane@acme.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "1234567890"
}
```

### Get Profile
Get details of the currently authenticated user.

- **URL:** `/api/profile`
- **Method:** `GET`
- **Auth Required:** Yes

---

## 3. Project Module

### List Projects
Get a list of all projects for the current business.

- **URL:** `/api/projects`
- **Method:** `GET`
- **Auth Required:** Yes

**Query Parameters:**
- `status` (optional): Filter by status (`active`, `completed`, `on_hold`, `cancelled`)
- `client_id` (optional): Filter by client

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Website Redesign",
      "code": "WEB-001",
      "client_id": 5,
      "status": "active",
      "client": { "id": 5, "name": "Client A" }
    }
  ]
}
```

### Create Project
Create a new project.

- **URL:** `/api/projects`
- **Method:** `POST`
- **Auth Required:** Yes (Business Admin / System Admin)

**Request Body:**
```json
{
  "name": "Mobile App Dev",
  "code": "APP-2025",
  "client_id": 5,
  "start_date": "2025-01-01",
  "end_date": "2025-06-30",
  "status": "active",
  "description": "Development of iOS and Android apps"
}
```

### View Project Details
- **URL:** `/api/projects/{id}`
- **Method:** `GET`
- **Auth Required:** Yes

### Update Project
- **URL:** `/api/projects/{id}`
- **Method:** `POST` (Note: Using POST for updates)
- **Auth Required:** Yes (Business Admin / System Admin)

**Request Body:**
```json
{
  "name": "Updated Project Name",
  "status": "completed"
}
```

### Delete Project
- **URL:** `/api/projects/{id}`
- **Method:** `DELETE`
- **Auth Required:** Yes (Business Admin / System Admin)

---

## 4. Holiday Module

### List Holidays
Get a list of holidays.

- **URL:** `/api/holidays`
- **Method:** `GET`
- **Auth Required:** Yes

**Query Parameters:**
- `year` (optional): Filter by year (e.g., 2025)
- `month` (optional): Filter by month (e.g., 12)

### Create Holiday
- **URL:** `/api/holidays`
- **Method:** `POST`
- **Auth Required:** Yes (Business Admin)

**Request Body:**
```json
{
  "name": "New Year",
  "date": "2025-01-01",
  "type": "public",
  "description": "Public Holiday"
}
```

### Delete Holiday
- **URL:** `/api/holidays/{id}`
- **Method:** `DELETE`
- **Auth Required:** Yes (Business Admin)

---

## 5. Timesheet Module

### Create Timesheet
Submit a new timesheet.

- **URL:** `/api/timesheet`
- **Method:** `POST`
- **Auth Required:** Yes

**Request Body:**
```json
{
  "user_id": 1,
  "client_id": 5,
  "project_id": 10,
  "start_date": "2025-11-24",
  "end_date": "2025-11-30",
  "status": "submitted",
  "remarks": "Weekly work",
  "entries": [
    {
      "entry_date": "2025-11-24",
      "daily_hours": 8,
      "extra_hours": 0,
      "note": "Frontend Dev"
    },
    {
      "entry_date": "2025-11-25",
      "daily_hours": 8,
      "extra_hours": 1,
      "note": "Bug Fixing"
    }
  ]
}
```

**Validation Rules:**
- `project_id` & `client_id` must belong to the user's business.
- `entry_date` cannot be a holiday.
- `daily_hours` + `extra_hours` cannot exceed 24.

### List Timesheets
- **URL:** `/api/timesheet`
- **Method:** `GET`
- **Query Params:** `status`, `user_id`, `project_id`, `from_date`, `to_date`

### Update Timesheet
Update a **draft** timesheet.

- **URL:** `/api/timesheet/{id}`
- **Method:** `PUT`
- **Auth Required:** Yes (Owner of timesheet)

### Status Update (Approve/Reject)
- **URL:** `/api/timesheet/{id}`
- **Method:** `PATCH`
- **Auth Required:** Yes (Business Admin / Approver)

**Request Body:**
```json
{
  "status": "approved" 
  // or "rejected", "submitted"
}
```

---

## 6. Attachment Module

### Upload Attachment
Upload a file related to a resource (e.g., Timesheet).

- **URL:** `/api/attachments`
- **Method:** `POST`
- **Auth Required:** Yes
- **Headers:** `Content-Type: multipart/form-data`

**Request Body:**
- `file`: (Binary File, max 10MB)
- `attachable_type`: `App\Models\Timesheet`
- `attachable_id`: `1`

### Download Attachment
- **URL:** `/api/attachments/{id}`
- **Method:** `GET`
- **Auth Required:** Yes

### Delete Attachment
- **URL:** `/api/attachments/{id}`
- **Method:** `DELETE`
- **Auth Required:** Yes

---

## 7. Dashboard Module

### Get Dashboard Stats
Get summary statistics for the dashboard.

- **URL:** `/api/dashboard`
- **Method:** `GET`
- **Auth Required:** Yes

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "total_employees": 15,
    "total_projects": 4,
    "submitted_timesheets": 5,
    "approved_timesheets": 20,
    "pending_approvals": 5,
    "monthly_overview": [
      { "month": "2025-10", "count": 12 },
      { "month": "2025-11", "count": 8 }
    ]
  }
}
```

---

## 8. Reports Module

### Generate Report
Export timesheet data in various formats.

- **URL:** `/api/reports`
- **Method:** `GET`
- **Auth Required:** Yes

**Query Parameters:**
- `type`: `pdf`, `excel`, or `csv` (Required for export)
- `start_date`: `YYYY-MM-DD`
- `end_date`: `YYYY-MM-DD`
- `user_id`: Filter by user
- `project_id`: Filter by project
- `status`: Filter by status

**Example:**
`GET /api/reports?type=pdf&start_date=2025-11-01&end_date=2025-11-30&status=approved`

---

## 9. Error Codes

| Status Code | Meaning | Description |
| :--- | :--- | :--- |
| **200** | OK | Request successful. |
| **201** | Created | Resource created successfully. |
| **400** | Bad Request | Invalid request logic (e.g., editing a submitted timesheet). |
| **401** | Unauthorized | Invalid or missing JWT token. |
| **403** | Forbidden | User does not have permission (e.g., accessing another business's data). |
| **404** | Not Found | Resource not found. |
| **422** | Unprocessable Entity | Validation failed (check `errors` object). |
| **500** | Server Error | Internal system error. |
