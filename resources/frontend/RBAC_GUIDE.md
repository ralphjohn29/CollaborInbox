# Role-Based Access Control (RBAC) System

This guide explains the role-based access control system implemented in CollaborInbox, including both backend and frontend components.

## Overview

The RBAC system in CollaborInbox provides a comprehensive approach to managing user permissions based on assigned roles. This system ensures that users can only access features and data they are authorized to use, maintaining security and proper data isolation within each tenant.

## Backend Components

### Models

1. **Role Model** (`app/Models/Role.php`)
   - Represents a user role (e.g., admin, team_lead, agent)
   - Contains methods for managing permissions:
     - `givePermissionTo($permission)`: Assigns a permission to the role
     - `revokePermissionTo($permission)`: Removes a permission from the role
     - `hasPermissionTo($permission)`: Checks if the role has a specific permission

2. **Permission Model** (`app/Models/Permission.php`)
   - Represents a granular permission (e.g., view_threads, edit_users)
   - Simple model with a many-to-many relationship to roles

3. **User Model** (`app/Models/User.php`)
   - Contains role relationship and permission checking methods:
     - `hasRole($roleName)`: Checks if the user has a specific role
     - `hasAnyRole($roleNames)`: Checks if the user has any of the given roles
     - `hasPermission($permissionName)`: Checks if the user has a specific permission

### Middleware

1. **RoleMiddleware** (`app/Http/Middleware/RoleMiddleware.php`)
   - Protects routes based on user roles
   - Usage in routes: `->middleware('role:admin')`

2. **PermissionMiddleware** (`app/Http/Middleware/PermissionMiddleware.php`)
   - Protects routes based on user permissions
   - Usage in routes: `->middleware('permission:edit_users')`

### Controllers

1. **RoleController** (`app/Http/Controllers/RoleController.php`)
   - Handles CRUD operations for roles
   - Manages assigning permissions to roles
   - Handles assigning roles to users

### Database Structure

1. **roles**: Stores role definitions
   - `id`: Primary key
   - `name`: Unique role name
   - `guard_name`: The authentication guard
   - `description`: Human-readable role description

2. **permissions**: Stores permission definitions
   - `id`: Primary key
   - `name`: Unique permission name
   - `guard_name`: The authentication guard
   - `description`: Human-readable permission description

3. **role_permission**: Pivot table for role-permission relationships
   - `role_id`: Foreign key to roles table
   - `permission_id`: Foreign key to permissions table

4. **users**: Contains role assignment
   - `role_id`: Foreign key to roles table

## Frontend Components

### Services

1. **RoleManager** (`resources/frontend/RoleManager.js`)
   - Core service for role and permission management
   - Methods:
     - `init()`: Initialize the role manager
     - `getRoles()`: Get all available roles
     - `getPermissions()`: Get all available permissions
     - `hasRole(roleName)`: Check if current user has a specific role
     - `hasPermission(permissionName)`: Check if current user has a specific permission
     - `assignRole(userId, roleId)`: Assign a role to a user
     - `updateRolePermissions(roleId, permissionIds)`: Update permissions for a role

### UI Components

1. **RoleSelector** (`resources/frontend/RoleSelector.js`)
   - Dropdown interface for selecting and assigning roles
   - Shows role description and permissions

2. **PermissionManager** (`resources/frontend/PermissionManager.js`)
   - Interface for managing permissions assigned to roles
   - Groups permissions by category for easier management

3. **RoleManagement** (`resources/frontend/RoleManagement.js`)
   - Comprehensive role management interface
   - Combines RoleSelector and PermissionManager

4. **PermissionDirective** (`resources/frontend/PermissionDirective.js`)
   - Provides declarative permission checks in HTML
   - Uses data attributes like `data-requires-permission` and `data-requires-role`
   - Actions: hide, remove, disable

## Default Roles and Permissions

### Roles

1. **Admin**
   - Full access to all features
   - Can manage users, roles, and all system settings

2. **Team Lead**
   - Can manage agents and threads
   - Limited access to system configuration

3. **Agent**
   - Basic access to handle threads and messages
   - Cannot access system administration features

### Permissions

The system includes permissions for managing:
- Users (view, create, edit, delete)
- Roles (view, create, edit, delete)
- Threads (view, create, edit, delete, assign)
- Messages (view, create, edit, delete)
- Notes (view, create, edit, delete)
- Mailboxes (view, create, edit, delete, test)

## Usage Examples

### Backend Route Protection

```php
// Protect a route with role middleware
Route::get('/admin/settings', [SettingsController::class, 'index'])
    ->middleware('role:admin');

// Protect a route with permission middleware
Route::post('/threads/{thread}/assign', [ThreadController::class, 'assign'])
    ->middleware('permission:assign threads');

// Use in controllers
public function update(Request $request, $id)
{
    // Manual permission check
    if (!auth()->user()->hasPermission('edit users')) {
        abort(403, 'Unauthorized');
    }
    
    // Continue with the action...
}
```

### Frontend Permission Checks

```javascript
// JavaScript permission check
import { RoleManager } from './index.js';

async function setupUI() {
    // Initialize
    await RoleManager.init();
    
    // Check permission
    if (await RoleManager.hasPermission('edit threads')) {
        document.getElementById('edit-button').style.display = 'block';
    }
}

// HTML declarative permission check
<button data-requires-permission="edit threads" data-permission-action="hide">
    Edit Thread
</button>

<div data-requires-role="admin" data-role-action="remove">
    Admin-only content
</div>
```

## Security Considerations

1. Always implement server-side permission checks in addition to client-side UI controls
2. Never rely solely on frontend permission checks for security
3. Remember that all client-side permission checks are for UI purposes only
4. Apply proper tenant isolation to ensure users can only access their tenant's data

## Best Practices

1. Use the most restrictive permissions needed for each role
2. Regularly audit role assignments and permissions
3. Prefer permission middleware over role middleware for more granular control
4. Keep permission names consistent and descriptive
5. Document new permissions as they are added to the system 