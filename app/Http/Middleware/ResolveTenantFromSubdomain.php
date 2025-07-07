<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromSubdomain
{
    /**
     * @var TenantManager
     */
    protected $tenantManager;

    /**
     * Constructor
     *
     * @param TenantManager $tenantManager
     */
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
        try {
            // Log request info for all requests to help debug tenant resolution
            Log::debug('Tenant resolution request', [
                'host' => $request->getHost(),
                'path' => $request->path(),
                'method' => $request->method(),
                'is_login' => $request->is('login') ? 'yes' : 'no',
                'has_subdomain' => $this->isTenantSubdomain($request) ? 'yes' : 'no'
            ]);
            
            // Allow login page on tenant subdomains always - never redirect login pages
            if ($request->is('login') && $this->isTenantSubdomain($request)) {
                Log::debug('Allowing tenant login page without redirection');
                $tenant = $this->tenantManager->resolveTenantFromRequest($request);
                if ($tenant) {
                    Log::debug('Tenant found for login page', [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'tenant_domain' => $tenant->domain ?? 'not set'
                    ]);
                    // Set the tenant in the manager but always continue
                    $this->tenantManager->setCurrentTenant($tenant);
                } else {
                    Log::debug('No tenant found for subdomain', [
                        'host' => $request->getHost(),
                        'subdomain' => $this->tenantManager->parseSubdomain($request->getHost())
                    ]);
                    // Even if no tenant is found for this subdomain, we should NOT redirect 
                    // the login page - let the user attempt to login
                }
                return $next($request);
            }
            
            // Skip tenant resolution for central domain's login route to avoid loops
            if ($request->is('login') && !$this->isTenantSubdomain($request)) {
                Log::debug('Skipping tenant resolution for central login');
                return $next($request);
            }
            
            $tenant = $this->tenantManager->resolveTenantFromRequest($request);
            
            // Log the tenant resolution result for debugging
            Log::debug('Tenant resolution result', [
                'host' => $request->getHost(),
                'path' => $request->path(),
                'tenant_found' => $tenant ? 'yes' : 'no',
                'tenant_id' => $tenant ? $tenant->id : null,
                'tenant_required' => $this->isTenantRequired($request) ? 'yes' : 'no',
                'is_tenant_subdomain' => $this->isTenantSubdomain($request) ? 'yes' : 'no'
            ]);
            
            // If no tenant found and subdomain is required, redirect to central domain or show error
            // But never redirect login pages
            if (!$tenant && $this->isTenantRequired($request) && $this->isTenantSubdomain($request) && !$request->is('login')) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Tenant not found.'], 404);
                }
                
                // Create a flash message first
                session()->flash('error', 'Tenant not found. Please login through the main website or contact your administrator.');
                
                // Get the main domain from the app.url config
                $baseDomain = parse_url(config('app.url'), PHP_URL_HOST);
                
                // Get the scheme from the current request
                $scheme = $request->secure() ? 'https' : 'http';
                
                // Get the port from the current request
                $port = $request->getPort();
                $portSuffix = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
                
                // Redirect to central domain login page directly
                $redirectUrl = $scheme . '://' . $baseDomain . $portSuffix . '/login';
                
                Log::debug('Redirecting to central domain', ['url' => $redirectUrl]);
                return redirect()->to($redirectUrl);
            }
            
            return $next($request);
        } catch (\Exception $e) {
            // Log the error with detailed information
            Log::error('Error in tenant resolution middleware', [
                'message' => $e->getMessage(),
                'host' => $request->getHost(),
                'path' => $request->path(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'There was an error processing your request. Please try again later.',
                    'error' => config('app.debug') ? $e->getMessage() : 'System error'
                ], 500);
            }
            
            // Create a flash message first
            session()->flash('error', 'There was an error processing your request. Please try again later.');
            
            // Get the main domain from the app.url config
            $baseDomain = parse_url(config('app.url'), PHP_URL_HOST);
            
            // Get the scheme from the current request
            $scheme = $request->secure() ? 'https' : 'http';
            
            // Get the port from the current request
            $port = $request->getPort();
            $portSuffix = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            
            // Redirect to central domain login page directly
            $redirectUrl = $scheme . '://' . $baseDomain . $portSuffix . '/login';
            
            Log::debug('Error redirect to central domain', ['url' => $redirectUrl]);
            return redirect()->to($redirectUrl);
        }
    }
    
    /**
     * Determine if a tenant is required for this request
     *
     * @param Request $request
     * @return bool
     */
    protected function isTenantRequired(Request $request): bool
    {
        // You can exclude routes that don't require a tenant, like central domain routes
        $excludedPaths = [
            'api/central',
            'central',
            'admin',
            '_debugbar',
        ];

        foreach ($excludedPaths as $path) {
            if ($request->is($path) || $request->is($path . '/*')) {
                return false;
            }
        }

        // Only require tenant for specific routes when on subdomain
        if ($this->isTenantSubdomain($request)) {
            return !in_array($request->path(), ['login', 'register', 'forgot-password']);
        }

        return false;
    }

    /**
     * Determine if the request is coming from a tenant subdomain 
     *
     * @param Request $request
     * @return bool
     */
    protected function isTenantSubdomain(Request $request): bool
    {
        $host = $request->getHost();
        $subdomain = $this->tenantManager->parseSubdomain($host);
        Log::debug('Checking if tenant subdomain', ['host' => $host, 'subdomain' => $subdomain]);
        return $subdomain !== null;
    }
}
