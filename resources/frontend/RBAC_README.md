# Role-Based Access Control (RBAC) Components

This directory contains frontend components for implementing role-based access control (RBAC) in the CollaborInbox application. These components provide an easy way to manage roles, permissions, and access control throughout the application.

## Core Components

### RoleManager

The `RoleManager` is a singleton service that provides role and permission management functionality. It handles:

- Fetching available roles and permissions from the backend
- Checking if a user has a specific role or permission
- Assigning roles to users
- Updating permissions for roles

```javascript
import { RoleManager } from './index.js';

// Check if the current user has a permission
const canEditUsers = await RoleManager.hasPermission('edit users');
if (canEditUsers) {
    // Show or enable the user edit functionality
}
```

### RoleSelector

A UI component for selecting and assigning roles. Features:

- Dropdown interface for role selection
- Role description display
- Permission list for each role
- Role assignment functionality (for user edit screens)

```javascript
import { RoleSelector } from './index.js';

// Create a role selector for user ID 123
new RoleSelector({
    containerId: 'role-selector-container',
    userId: 123,
    selectedRoleId: 2,
    onChange: (roleId) => {
        console.log('Selected role changed to:', roleId);
    }
});
```

### PermissionManager

A UI component for managing permissions assigned to roles. Features:

- Grouped permission display by category
- Checkbox interface for selecting permissions
- Save functionality for updating role permissions

```javascript
import { PermissionManager } from './index.js';

// Create a permission manager for role ID 1 (admin)
new PermissionManager({
    containerId: 'permission-manager-container',
    roleId: 1,
    onSave: (permissions) => {
        console.log('Permissions saved:', permissions);
    }
});
```

### RoleManagement

A comprehensive component that combines RoleSelector and PermissionManager. Features:

- Role selection sidebar
- Permission management interface
- Complete role and permission administration

```javascript
import { RoleManagement } from './index.js';

// Initialize the complete role management interface
new RoleManagement({
    containerId: 'role-management-container'
});
```

### PermissionDirective

A utility for declarative permission checks in HTML using data attributes. Features:

- Permission-based element visibility
- Role-based element visibility
- Configurable actions (hide, remove, disable)
- Automatic processing of dynamically added elements

```html
<!-- Example usage in HTML -->
<button data-requires-permission="edit users" data-permission-action="hide">
    Edit User
</button>

<div data-requires-role="admin" data-role-action="remove">
    Admin-only content
</div>
```

## Installation

1. Import the required components from the index.js file:

```javascript
import { 
    RoleManager, 
    RoleSelector, 
    PermissionManager, 
    RoleManagement, 
    PermissionDirective 
} from './index.js';
```

2. Ensure the CSS files are imported (or compile them with your build system):

```javascript
import './role-management.css';
```

## Backend API Requirements

These components expect the following API endpoints:

- `GET /api/roles` - Returns all roles with their permissions
- `GET /api/user` - Returns current user information including role
- `PUT /api/users/{id}/role` - Assigns a role to a user
- `PUT /api/roles/{id}/permissions` - Updates permissions for a role

## Usage Examples

### Basic Permission Check

```javascript
import { RoleManager } from './index.js';

async function setupUI() {
    await RoleManager.init();
    
    // Check if user can access a feature
    if (await RoleManager.hasPermission('view threads')) {
        document.getElementById('threads-section').style.display = 'block';
    }
    
    // Check if user has admin role
    if (await RoleManager.hasRole('admin')) {
        document.getElementById('admin-panel').style.display = 'block';
    }
}
```

### User Role Assignment

```javascript
// In a user edit page
new RoleSelector({
    containerId: 'user-role-container',
    userId: userId, // From your application context
    selectedRoleId: currentRoleId // From your application context
});
```

### Complete Role Management Page

```javascript
// In an admin settings page
new RoleManagement({
    containerId: 'admin-roles-container'
});
```

### HTML-Based Permission Checks

```html
<!-- Button only visible to users with 'edit threads' permission -->
<button data-requires-permission="edit threads">
    Edit Thread
</button>

<!-- Section only visible to team_lead or admin roles -->
<section data-requires-role="team_lead" data-role-action="hide">
    <h2>Team Management</h2>
    <!-- Team management content -->
</section>
```

## Security Considerations

1. Always implement server-side permission checks in addition to client-side controls
2. Never rely solely on frontend permission checks for security
3. Backend API endpoints should verify permissions before allowing actions
4. Remember that client-side permission checks are for UI purposes only 