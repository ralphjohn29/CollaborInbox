<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        
        // Add debug logging for route mapping
        Log::info('Route Service Provider boot method called');
        
        $this->mapRoutes();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Define the routes for the application.
     */
    protected function mapRoutes(): void
    {
        // Central domains routes (non-tenant)
        Log::info('Mapping API routes');
        $this->mapApiRoutes();
        
        Log::info('Mapping Web routes');
        $this->mapWebRoutes();
        
        // Test routes
        Log::info('Mapping Test routes');
        $this->mapTestRoutes();
        
        // Tenant routes (only accessible on tenant domains)
        Log::info('Mapping Tenant routes');
        $this->mapTenantRoutes();
    }

    /**
     * Define the "web" routes for the application.
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(function() {
                Log::info('Loading web routes from: ' . base_path('routes/web.php'));
                require base_path('routes/web.php');
            });
    }

    /**
     * Define the "api" routes for the application.
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the test routes for the application.
     */
    protected function mapTestRoutes(): void
    {
        $testRoutePath = base_path('routes/test.php');
        Log::info('Loading test routes from: ' . $testRoutePath);
        
        if (file_exists($testRoutePath)) {
            Route::middleware('web')
                ->group(function() {
                    Log::info('Loading test routes file');
                    require base_path('routes/test.php');
                });
        }
    }

    /**
     * Define the "tenant" routes for the application.
     * These routes are accessible only on tenant domains.
     */
    protected function mapTenantRoutes(): void
    {
        // Only register tenant routes if the file exists
        $tenantRoutePath = base_path('routes/tenant.php');
        Log::info('Checking for tenant routes file: ' . $tenantRoutePath . ', exists: ' . (file_exists($tenantRoutePath) ? 'yes' : 'no'));
        
        if (file_exists($tenantRoutePath)) {
            // This ensures tenant routes are only loaded for subdomain requests
            // The subdomain middleware will handle the actual tenant resolution
            $baseHost = parse_url(config('app.url'), PHP_URL_HOST);
            $currentHost = request()->getHost();
            
            Log::info('Tenant route mapping - Base host: ' . $baseHost . ', Current host: ' . $currentHost);
            
            // Only apply tenant routes if we're NOT on the main domain
            if ($currentHost !== $baseHost) {
                Route::middleware(['web', 'tenant'])
                    ->group($tenantRoutePath);
                
                Log::info('Tenant routes registered for: ' . $currentHost);
            } else {
                Log::info('Skipping tenant routes for main domain: ' . $baseHost);
            }
        }
    }
} 