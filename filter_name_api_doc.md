# API Filtering Documentation

This document outlines the filtering capabilities available for the Timesheet and Chart APIs.

## 1. Timesheet Management API

### Endpoint: `GET /api/timesheets`

This endpoint retrieves a list of timesheets.

| Parameter   | Type      | Description                                                                       | Example                 |
| :---------- | :-------- | :-------------------------------------------------------------------------------- | :---------------------- |
| `status`    | `string`  | Filter by timesheet status. Options: `draft`, `submitted`, `approved`, `rejected` | `?status=approved`      |
| `user_id`   | `integer` | Filter by user ID. (Restricted for 'User' role to own ID)                         | `?user_id=12`           |
| `client_id` | `integer` | Filter by client (party) ID.                                                      | `?client_id=5`          |
| `from_date` | `date`    | Filter records with `start_date` on or after this date. Format: `YYYY-MM-DD`      | `?from_date=2025-01-01` |
| `to_date`   | `date`    | Filter records with `end_date` on or before this date. Format: `YYYY-MM-DD`       | `?to_date=2025-01-31`   |

---

### Endpoint: `GET /api/timesheets/scheduler`

This endpoint retrieves timesheet entries for the scheduler view.

| Parameter    | Type      | Description                                               | Example                  |
| :----------- | :-------- | :-------------------------------------------------------- | :----------------------- |
| `date`       | `date`    | Filter entries for a specific date. Format: `YYYY-MM-DD`  | `?date=2025-01-15`       |
| `start_date` | `date`    | Start date for a range filter.                            | `?start_date=2025-01-01` |
| `end_date`   | `date`    | End date for a range filter.                              | `?end_date=2025-01-07`   |
| `month`      | `string`  | Filter entries for a specific month. Format: `YYYY-MM`    | `?month=2025-01`         |
| `user_id`    | `integer` | Filter by user ID. (Restricted for 'User' role to own ID) | `?user_id=5`             |

---

## 2. Chart & Analytics API

### Endpoint: `GET /api/charts/summary` (Dashboard Stats)

### Endpoint: `GET /api/charts/trend` (Trend Charts)

Both endpoints share the same filtering logic.

| Parameter    | Type      | Description                                                                       | Example                  |
| :----------- | :-------- | :-------------------------------------------------------------------------------- | :----------------------- |
| `status`     | `string`  | Filter by timesheet status. Options: `draft`, `submitted`, `approved`, `rejected` | `?status=completed`      |
| `user_id`    | `integer` | Filter by user ID. (Restricted for 'User' role to own ID)                         | `?user_id=10`            |
| `client_id`  | `integer` | Filter by client (party) ID.                                                      | `?client_id=3`           |
| `start_date` | `date`    | Filter timesheets with `start_date` on or after this date.                        | `?start_date=2025-01-01` |
| `end_date`   | `date`    | Filter timesheets with `end_date` on or before this date.                         | `?end_date=2025-12-31`   |
| `month`      | `string`  | Filter timesheets where `start_date` is in this month. Format: `YYYY-MM`          | `?month=2025-01`         |
| `year`       | `integer` | Filter timesheets where `start_date` is in this year. Format: `YYYY`              | `?year=2025`             |

#### Trend Chart Specific Parameter:

| Parameter  | Type     | Description                                                                       | Default | Example          |
| :--------- | :------- | :-------------------------------------------------------------------------------- | :------ | :--------------- |
| `group_by` | `string` | Determines how the trend data is grouped. Options: `day`, `week`, `month`, `year` | `month` | `?group_by=week` |

### Notes:

-   **Business Logic**: All endpoints automatically filter data based on the authenticated user's `business_id`.
-   **User Role Restriction**: Users with the `User` role are automatically restricted to viewing only their own data (`user_id` matches their auth ID), regardless of the `user_id` parameter passed.
