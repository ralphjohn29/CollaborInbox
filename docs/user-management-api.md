# User Management API Documentation

## Overview

The User Management API provides comprehensive CRUD operations for managing users in the CollaborInbox system. All endpoints require authentication via Laravel Sanctum.

## Base URL

```
http://localhost:8000/api
```

## Authentication

All API endpoints require authentication. Include the authentication token in the header:

```
Authorization: Bearer YOUR_API_TOKEN
```

## Endpoints

### 1. List All Users

Get a paginated list of all users with filtering and sorting options.

**Endpoint:** `GET /api/users`

**Query Parameters:**
- `search` (string, optional): Search by name or email
- `role_id` (integer, optional): Filter by role ID
- `is_active` (boolean, optional): Filter by active status
- `is_admin` (boolean, optional): Filter by admin status
- `sort_by` (string, optional): Sort field (default: 'created_at')
- `sort_order` (string, optional): Sort order 'asc' or 'desc' (default: 'desc')
- `per_page` (integer, optional): Items per page (default: 15)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/users?search=john&is_active=true&per_page=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role_id": 1,
        "tenant_id": null,
        "is_active": true,
        "is_admin": false,
        "created_at": "2024-01-10T10:00:00.000000Z",
        "updated_at": "2024-01-10T10:00:00.000000Z",
        "role": {
          "id": 1,
          "name": "user",
          "guard_name": "web",
          "description": "Regular user role"
        },
        "tenant": null
      }
    ],
    "total": 10,
    "per_page": 10,
    "last_page": 1
  },
  "message": "Users retrieved successfully"
}
```

### 2. Create User

Create a new user account.

**Endpoint:** `POST /api/users`

**Request Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 1,
  "tenant_id": null,
  "is_active": true,
  "is_admin": false
}
```

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/users" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role_id": 1
  }'
```

### 3. Get User Details

Get details of a specific user.

**Endpoint:** `GET /api/users/{id}`

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/users/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### 4. Update User

Update an existing user's information.

**Endpoint:** `PUT /api/users/{id}`

**Request Body (all fields optional):**
```json
{
  "name": "Jane Doe Updated",
  "email": "jane.updated@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123",
  "role_id": 2,
  "is_active": true,
  "is_admin": false
}
```

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/users/1" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "name": "Jane Doe Updated",
    "email": "jane.updated@example.com"
  }'
```

### 5. Delete User

Delete a user account.

**Endpoint:** `DELETE /api/users/{id}`

**Example Request:**
```bash
curl -X DELETE "http://localhost:8000/api/users/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### 6. Toggle User Status

Toggle a user's active/inactive status.

**Endpoint:** `PATCH /api/users/{id}/toggle-status`

**Example Request:**
```bash
curl -X PATCH "http://localhost:8000/api/users/1/toggle-status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### 7. Update User Password

Update a user's password (separate endpoint for password-only updates).

**Endpoint:** `PUT /api/users/{id}/password`

**Request Body:**
```json
{
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/users/1/password" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

### 8. Get All Users with Password Hashes (Admin Only)

Retrieve all users with their hashed passwords. This endpoint is restricted to admin users only.

**Endpoint:** `GET /api/users/passwords/all`

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/users/passwords/all" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "password_hash": "$2y$12$...",
      "role": "user",
      "tenant": null,
      "is_active": true,
      "is_admin": false,
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z"
    }
  ],
  "message": "Users with password hashes retrieved successfully",
  "warning": "These are hashed passwords. Original passwords cannot be retrieved."
}
```

### 9. Bulk Operations

Perform bulk operations on multiple users.

**Endpoint:** `POST /api/users/bulk`

**Request Body:**
```json
{
  "action": "activate", // Options: activate, deactivate, delete, assign_role
  "user_ids": [1, 2, 3],
  "role_id": 2 // Required only when action is "assign_role"
}
```

**Example Requests:**

**Bulk Activate Users:**
```bash
curl -X POST "http://localhost:8000/api/users/bulk" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "action": "activate",
    "user_ids": [1, 2, 3]
  }'
```

**Bulk Assign Role:**
```bash
curl -X POST "http://localhost:8000/api/users/bulk" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "action": "assign_role",
    "user_ids": [1, 2, 3],
    "role_id": 2
  }'
```

## Error Responses

All endpoints return consistent error responses:

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

**Not Found (404):**
```json
{
  "success": false,
  "message": "User not found"
}
```

**Server Error (500):**
```json
{
  "success": false,
  "message": "Failed to create user",
  "error": "Database connection error"
}
```

**Unauthorized (403):**
```json
{
  "success": false,
  "message": "You cannot delete your own account"
}
```

## Security Notes

1. **Password Storage**: All passwords are hashed using bcrypt before storage
2. **Self-Protection**: Users cannot delete or deactivate their own accounts
3. **Admin Access**: The password hash endpoint is restricted to admin users only
4. **Authentication**: All endpoints require valid authentication tokens
5. **Tenant Isolation**: Users are isolated by tenant when multi-tenancy is enabled

## Database Schema

The users table includes the following fields:
- `id` (bigint, primary key)
- `tenant_id` (bigint, nullable, foreign key)
- `role_id` (bigint, nullable, foreign key)
- `name` (string)
- `email` (string, unique)
- `email_verified_at` (timestamp, nullable)
- `password` (string, hashed)
- `is_active` (boolean, default: true)
- `is_admin` (boolean, default: false)
- `remember_token` (string, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

## Testing

To test the user management system, run:

```bash
docker exec -it collaborinbox-laravel.test-1 php test-user-simple.php
```

This will create test users and verify all CRUD operations are working correctly.
