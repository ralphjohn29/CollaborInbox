<?php

namespace App\Http\Middleware;

use App\Services\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Middleware\IdentificationMiddleware;
use Stancl\Tenancy\Tenancy;

class InitializeTenancyBySubdomain extends IdentificationMiddleware
{
    /** @var callable|null */
    public static $onFail;

    /** @var Tenancy */
    protected $tenancy;

    /** @var TenantResolver */
    protected $resolver;

    public function __construct(Tenancy $tenancy, TenantResolver $resolver)
    {
        $this->tenancy = $tenancy;
        $this->resolver = $resolver;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::debug('InitializeTenancyBySubdomain middleware', [
            'host' => $request->getHost(),
            'path' => $request->path(),
            'method' => $request->method(),
        ]);
        
        // Never redirect login pages from tenant subdomains
        if ($request->is('login')) {
            // Still try to find the tenant, but don't redirect if not found
            $tenant = $this->resolver->findTenantByRequest($request);
            if ($tenant) {
                Log::debug('Found tenant for login page', [
                    'tenant_id' => $tenant->id, 
                    'tenant_name' => $tenant->name
                ]);
                // Directly initialize tenancy and proceed
                $this->tenancy->initialize($tenant);
                return $next($request);
            }
            Log::debug('No tenant found for login page, but continuing without redirect');
            return $next($request);
        }
        
        $tenant = $this->resolver->findTenantByRequest($request);
        
        if ($tenant) {
            Log::debug('Tenant found, initializing tenancy', [
                'tenant_id' => $tenant->id, 
                'tenant_name' => $tenant->name
            ]);
            // Directly initialize tenancy and proceed
            $this->tenancy->initialize($tenant);
            return $next($request);
        }
        
        // If we're already on the main domain, just continue
        $mainHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($request->getHost() === $mainHost) {
            Log::debug('Already on main domain, continuing without redirect');
            return $next($request);
        }
        
        // If tenant could not be identified and we're not on the main domain
        // and not on a login page, redirect to main domain
        Log::debug('Tenant not found, considering redirect', [
            'current_host' => $request->getHost(), 
            'main_host' => $mainHost
        ]);

        // If tenant could not be identified, use the onFail callback
        // or proceed with the request if no callback is defined
        $onFail = static::$onFail ?? function ($exception, $request, $next) {
            return $next($request);
        };
        
        return $onFail(null, $request, $next);
    }
} 