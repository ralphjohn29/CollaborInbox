# Tenant Identification Setup

This document explains how to set up and test the tenant identification system via subdomains in CollaborInbox.

## Setup Process

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Set up Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure Database**
   Edit your `.env` file to set up the database connection:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=collaborinbox
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Set up Tenancy**
   ```bash
   php artisan tenancy:setup
   ```
   This command will:
   - Publish tenancy configuration
   - Create tenant migrations directory
   - Run the necessary migrations

5. **Configure Local Subdomains**
   Edit your hosts file (Windows: `C:\Windows\System32\drivers\etc\hosts`, macOS/Linux: `/etc/hosts`):
   ```
   127.0.0.1    collaborinbox.test
   127.0.0.1    tenant1.collaborinbox.test 
   127.0.0.1    tenant2.collaborinbox.test
   ```

## Creating Tenants

Create tenants using the provided command:

```bash
php artisan tenant:create --name="Tenant 1" --domain=tenant1
php artisan tenant:create --name="Tenant 2" --domain=tenant2
```

## Testing Tenant Identification

1. **Start the server**
   ```bash
   php artisan serve --host=collaborinbox.test
   ```

2. **Test tenant routes**
   Visit these URLs in your browser:
   - http://tenant1.collaborinbox.test:8000/tenant-info
   - http://tenant2.collaborinbox.test:8000/tenant-info
   - http://tenant1.collaborinbox.test:8000/dashboard
   - http://collaborinbox.test:8000/ (central domain)

Each tenant subdomain should correctly identify the specific tenant and return the appropriate tenant information.

## Troubleshooting

- **Database Connection Issues**: Ensure your database exists and credentials are correct
- **Subdomain Not Working**: Check your hosts file has the correct entries
- **404 Errors**: Ensure the routes are defined in `routes/tenant.php`
- **Tenant Not Found**: Verify the tenant exists in the database with `php artisan tinker` and `App\Models\Tenant::all()`

## How It Works

1. The incoming request's domain is captured by `InitializeTenancyByDomain` middleware
2. The middleware looks up the domain in the `domains` table
3. If found, it associates the tenant with the current request
4. The tenant's database is connected, and the application operates in tenant context
5. Routes in `routes/tenant.php` are only accessible on tenant domains

## Implementation Details

The key components of the implementation are:

1. **Tenant Model**: `App\Models\Tenant.php` - Extends `Stancl\Tenancy\Database\Models\Tenant`
2. **TenancyServiceProvider**: `App\Providers\TenancyServiceProvider.php` - Handles tenant events 
3. **RouteServiceProvider**: Updated to properly register tenant routes
4. **Middleware**: `InitializeTenancyByDomain` added to route groups
5. **Commands**: `CreateTenantCommand` for easily creating tenants 