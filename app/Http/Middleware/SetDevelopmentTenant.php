<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TenantManager;
use App\Models\Tenant;

class SetDevelopmentTenant
{
    protected $tenantManager;
    
    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only in local development
        if (app()->environment('local')) {
            // Check if we already have a tenant set
            if (!$this->tenantManager->getCurrentTenant()) {
                try {
                    // Get the first active tenant as default
                    $tenant = Tenant::where('is_active', true)->first();
                    
                    if ($tenant) {
                        $this->tenantManager->setCurrentTenant($tenant);
                        
                        // Also set it in the app container
                        app()->instance('tenant', $tenant);
                        config(['tenant.id' => $tenant->id]);
                        
                        \Log::info('Development tenant set', [
                            'tenant_id' => $tenant->id,
                            'tenant_name' => $tenant->name,
                            'path' => $request->path()
                        ]);
                    }
                } catch (\Exception $e) {
                    // Database connection failed - log and continue
                    \Log::warning('Could not set development tenant - database not available', [
                        'error' => $e->getMessage(),
                        'path' => $request->path()
                    ]);
                }
            }
        }
        
        return $next($request);
    }
}
