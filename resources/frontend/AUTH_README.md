# Authentication System for CollaborInbox

This document provides an overview of the authentication system implementation for CollaborInbox.

## Components

### 1. AuthService (AuthService.js)

A singleton service that manages authentication state and provides methods for:

- Login (`login(email, password, deviceName)`)
- Logout (`logout()`)
- Session management (`setSession()`, `clearSession()`)
- Authentication status checks (`isAuthenticated()`)
- User information retrieval (`getCurrentUser()`, `getCurrentTenant()`)
- Role checks (`hasRole(roleName)`)

### 2. LoginPage (pages/LoginPage.js)

A UI component that renders the login form with:

- Tenant detection from subdomain
- Form validation
- Error handling
- Loading states
- Remember me functionality

### 3. Backend Components

- `AuthController`: Handles API endpoints for authentication
- `auth/login.blade.php`: Server-side view template 
- API routes in `routes/api.php`
- Web routes in `routes/web.php`

## Usage

### Frontend Integration

```javascript
import authService from './AuthService.js';

// Check authentication status
if (authService.isAuthenticated()) {
  // User is logged in
  const user = authService.getCurrentUser();
  console.log(`Welcome back, ${user.name}!`);
}

// Login programmatically
authService.login('user@example.com', 'password')
  .then(response => {
    // Handle successful login
  })
  .catch(error => {
    // Handle login error
  });

// Logout
authService.logout()
  .then(() => {
    // Redirect to login page
    window.location.href = '/login';
  });

// Check user roles
if (authService.hasRole('admin')) {
  // Show admin features
}
```

### Protecting Routes

Frontend routes should check authentication status:

```javascript
// Example route guard
function checkAuth(nextPage) {
  if (!authService.isAuthenticated()) {
    // Redirect to login
    window.location.href = '/login';
    return false;
  }
  return true;
}
```

API routes are protected using Sanctum middleware:

```php
// Example from routes/api.php
Route::middleware(['auth:sanctum', 'tenant.resolve'])->group(function () {
    // Protected routes here
});
```

## Tenant-Aware Authentication

The authentication system is integrated with the multi-tenant architecture:

1. Tenant is detected from the subdomain during login
2. Users are verified to belong to the current tenant
3. All authenticated requests maintain tenant context
4. Token is scoped to the tenant it was created for

## Security Considerations

- CSRF protection is automatically applied to all requests
- Tokens are stored in localStorage and included in request headers
- Passwords are never stored client-side
- Failed login attempts are properly handled with informative error messages
- Remember me functionality uses device information to create more specific tokens

## Customization

The login page appearance can be customized by modifying:

- `resources/frontend/auth.css` for styling
- `resources/frontend/pages/LoginPage.js` for layout and behavior
- `resources/views/auth/login.blade.php` for the containing template 