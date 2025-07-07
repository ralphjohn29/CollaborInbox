<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Stancl\Tenancy\Resolvers\TenantResolver;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;

class SetTenantMiddleware
{
    /**
     * The tenancy instance.
     *
     * @var \Stancl\Tenancy\Tenancy
     */
    protected $tenancy;

    /**
     * Create a new middleware instance.
     *
     * @param  \Stancl\Tenancy\Tenancy  $tenancy
     * @return void
     */
    public function __construct(Tenancy $tenancy)
    {
        $this->tenancy = $tenancy;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->tenancy->initialized) {
            // Get the current tenant from tenancy
            $tenant = $this->tenancy->tenant;
            
            // Set the tenant in our context
            TenantContext::setTenant($tenant);
            
            // Bind the tenant to the container for dependency injection
            App::instance('currentTenant', $tenant);
        } else {
            // If no tenant was identified, clear the context
            TenantContext::clear();
        }

        // Get the response
        $response = $next($request);
        
        // You can add tenant-specific headers or processing here if needed
        if (TenantContext::hasTenant()) {
            $response->headers->set('X-Tenant-ID', TenantContext::getTenantId());
        }

        return $response;
    }
} 