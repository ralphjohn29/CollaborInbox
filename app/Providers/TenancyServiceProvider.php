<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyBootstrapped;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Illuminate\Support\Facades\Artisan;

class TenancyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Boot tasks for tenant creation
        $this->bootstrapTenantEvents();
        
        // Central routes use the default Laravel middleware (web, api, etc).
        // Tenant routes use InitializeTenancyByDomain + PreventAccessFromCentralDomains
        // All of this is handled in RouteServiceProvider
    }

    /**
     * Set up tenant creation database initialization
     */
    protected function bootstrapTenantEvents()
    {
        // When a tenant is created, we want to automatically create a database for it
        Event::listen(TenantCreated::class, function (TenantCreated $event) {
            // Run inside a job to prevent request timeouts
            CreateDatabase::dispatch($event->tenant);
            
            // Run migrations for the tenant
            $this->runTenantMigrations($event->tenant);
            
            // You could add additional initialization logic here, such as:
            // - Create default users/data
            // - Send welcome emails
        });
        
        // Optional: Log when a tenant is initialized
        Event::listen(TenancyBootstrapped::class, function (TenancyBootstrapped $event) {
            // This event is fired when tenant is resolved and bootstrapped
            // You could add logging, metrics, or other tracking here
        });
    }
    
    /**
     * Run migrations for a newly created tenant
     *
     * @param \App\Models\Tenant $tenant
     * @return void
     */
    protected function runTenantMigrations($tenant)
    {
        $tenant->run(function () {
            // Get migration files from the tenant migrations directory
            $migrationPath = database_path('migrations/tenant');
            
            // Run the migrations in the tenant context
            Artisan::call('migrate', [
                '--path' => str_replace(base_path().'/', '', $migrationPath),
                '--force' => true,
            ]);
        });
    }
} 