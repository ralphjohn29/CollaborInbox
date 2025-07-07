# CollaborInbox - Multi-Tenant Setup

This document outlines the setup and configuration of the multi-tenant system in CollaborInbox.

## System Overview

CollaborInbox uses a multi-tenant architecture with subdomain routing:
- Each company has its own subdomain (e.g., `acme.collaborinbox.test`).
- Data is isolated per tenant.
- The application identifies tenants based on the incoming request domain.

## Configuration Files

The multi-tenant system relies on the following configuration files:
- `config/tenancy.php`: Main configuration file for the tenancy system.
- `app/Models/Tenant.php`: The Tenant model that implements required interfaces.
- `app/Http/Middleware/TenantMiddleware.php`: Middleware to identify and set the current tenant context.
- `app/Traits/BelongsToTenant.php`: Trait to automatically scope models to the current tenant.

## Local Development Setup

For local development, you need to set up your system to handle subdomains:

### Using Laravel Valet (macOS)

```bash
# Link your site
valet link collaborinbox

# Visit your tenant at
# tenant1.collaborinbox.test
```

### Using Laravel Homestead

Add the following to your Homestead.yaml file:
```yaml
sites:
    - map: collaborinbox.test
      to: /home/vagrant/code/collaborinbox/public
    - map: '*.collaborinbox.test'
      to: /home/vagrant/code/collaborinbox/public
```

### Manual hosts file setup (Windows, Linux)

Add entries in your hosts file:
```
127.0.0.1 collaborinbox.test
127.0.0.1 tenant1.collaborinbox.test
127.0.0.1 tenant2.collaborinbox.test
# Add more tenants as needed
```

## Creating Tenants

You can create a new tenant using the included Artisan command:

```bash
php artisan tenant:create "Company Name" "subdomain.collaborinbox.test"
```

## Testing Tenant Isolation

To test that data is properly isolated between tenants:

1. Create two or more tenants
2. Create data under one tenant (visit the tenant's subdomain)
3. Try to access that data from another tenant
4. Verify that the second tenant cannot access the first tenant's data

## Database Structure

All tenant-specific models should:
1. Include a `tenant_id` column
2. Use the `BelongsToTenant` trait to automatically scope queries and set the tenant_id on creation

Example migration for a tenant-aware model:

```php
Schema::create('threads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
    $table->string('subject');
    $table->timestamps();
});
```

Example model implementation:

```php
class Thread extends Model
{
    use BelongsToTenant;
    
    protected $fillable = ['subject'];
}
```

## Security Considerations

- Always use the `BelongsToTenant` trait on tenant-specific models
- Be careful with user-provided tenant IDs in any code
- Ensure all API endpoints are properly scoped to the current tenant
- Regularly check for possible data leaks between tenants

## Additional Resources

- [Tenancy for Laravel Documentation](https://tenancyforlaravel.com/docs/tenancy/3.x/)
- [Laravel Multi-Tenancy Best Practices](https://laravel-news.com/multi-tenancy-in-laravel) 