<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use Tenancy\Identification\Drivers\Domain\Contracts\IdentifiesByDomain; 
// use Tenancy\Identification\Drivers\Domain\Support\DomainIdentification; 
use App\Models\Tenant; 
use App\Services\TenantResolver;
use App\Services\TenantManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind(IdentifiesByDomain::class, function ($app) {
        //     return new DomainIdentification(Tenant::class, 'domain');
        // });

        $this->app->singleton(TenantResolver::class, function ($app) {
            return new TenantResolver();
        });
        
        // Register TenantManager as singleton to persist state across request
        $this->app->singleton(TenantManager::class, function ($app) {
            return new TenantManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
} 