<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TenantManager
{
    /**
     * @var Tenant|null
     */
    private $currentTenant = null;

    /**
     * Get the current tenant from the request subdomain
     * 
     * @param Request $request
     * @return Tenant|null
     */
    public function resolveTenantFromRequest(Request $request)
    {
        // Log request details for debugging
        Log::debug('Resolving tenant from request', [
            'host' => $request->getHost(),
            'method' => $request->method(),
            'path' => $request->path()
        ]);
        
        $subdomain = $this->parseSubdomain($request->getHost());
        
        if (!$subdomain) {
            Log::debug('No subdomain found in request');
            
            // In development, if no subdomain, use the default tenant
            if (app()->environment('local') && $this->shouldUseFallbackTenant($request)) {
                $tenant = $this->getDefaultTenant();
                if ($tenant) {
                    Log::debug('Using default tenant for development', [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name
                    ]);
                    $this->setCurrentTenant($tenant);
                    return $tenant;
                }
            }
            
            return null;
        }
        
        Log::debug('Subdomain detected', ['subdomain' => $subdomain]);
        
        try {
            $tenant = $this->getTenantBySubdomain($subdomain);
            
            if ($tenant) {
                $this->setCurrentTenant($tenant);
                return $tenant;
            }
            
            Log::debug('No tenant found for subdomain', ['subdomain' => $subdomain]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error resolving tenant', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Parse subdomain from host
     * 
     * @param string $host
     * @return string|null
     */
    public function parseSubdomain(string $host)
    {
        $parts = explode('.', $host);
        
        // Log the host and parts for debugging
        Log::debug('Parsing subdomain', [
            'host' => $host,
            'parts' => $parts,
            'count' => count($parts)
        ]);
        
        // Handle development domains like 'test.collaborinbox.test' (has exactly 3 parts)
        if (count($parts) === 3) {
            // Return the first part as the subdomain
            if ($parts[0] !== 'www') {
                Log::debug('Found subdomain in 3-part hostname', ['subdomain' => $parts[0]]);
                return $parts[0];
            }
        }
        
        // Handle production domains like 'tenant.example.com' (has 3 or more parts)
        if (count($parts) > 3) {
            // Exclude 'www' subdomain
            if ($parts[0] === 'www') {
                return null;
            }
            
            Log::debug('Found subdomain in multi-part hostname', ['subdomain' => $parts[0]]);
            return $parts[0];
        }
        
        Log::debug('No subdomain found in hostname');
        return null;
    }
    
    /**
     * Get tenant by subdomain
     * 
     * @param string $subdomain
     * @return Tenant|null
     */
    public function getTenantBySubdomain(string $subdomain)
    {
        $cacheKey = 'tenant:' . $subdomain;
        
        // Log cache key for debugging
        Log::debug('Attempting to get tenant by subdomain', [
            'subdomain' => $subdomain,
            'cache_key' => $cacheKey
        ]);
        
        // Clear cache for debugging if needed
        // Cache::forget($cacheKey);
        
        return Cache::remember($cacheKey, 60, function () use ($subdomain) {
            // Try looking for tenants with domains table entries first
            try {
                $tenant = Tenant::with('domains')
                    ->whereHas('domains', function ($query) use ($subdomain) {
                        $query->where('domain', $subdomain . '.collaborinbox.test')
                            ->orWhere('domain', $subdomain . '.collaborinbox.com');
                    })
                    ->first();
                
                if ($tenant) {
                    Log::debug('Found tenant by domain relationship', [
                        'subdomain' => $subdomain,
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'domains' => $tenant->domains->pluck('domain')
                    ]);
                    return $tenant;
                }
            } catch (\Exception $e) {
                Log::error('Error looking up tenant by domains relationship', [
                    'subdomain' => $subdomain,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Fallback to direct domain field on tenant model
            $tenant = Tenant::where('domain', $subdomain . '.collaborinbox.test')
                        ->orWhere('domain', $subdomain . '.collaborinbox.com')
                        ->orWhere('domain', 'LIKE', $subdomain . '.%')
                        ->first();
            
            if ($tenant) {
                Log::debug('Found tenant by direct domain match', [
                    'subdomain' => $subdomain,
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'tenant_domain' => $tenant->domain
                ]);
                return $tenant;
            }
            
            // Try direct name match (for development scenarios)
            $tenant = Tenant::where('name', 'LIKE', $subdomain)
                        ->orWhere('name', 'LIKE', $subdomain . '%')
                        ->first();
            
            if ($tenant) {
                Log::debug('Found tenant by name match', [
                    'subdomain' => $subdomain,
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'tenant_domain' => $tenant->domain
                ]);
                return $tenant;
            }
            
            // Last resort - try sanitized name match
            $allTenants = Tenant::all();
            foreach ($allTenants as $t) {
                $sanitizedName = strtolower(preg_replace('/[^a-z0-9]/i', '', $t->name));
                if ($sanitizedName === $subdomain) {
                    Log::debug('Found tenant by sanitized name match', [
                        'subdomain' => $subdomain,
                        'tenant_id' => $t->id,
                        'tenant_name' => $t->name,
                        'tenant_domain' => $t->domain,
                        'sanitized_name' => $sanitizedName
                    ]);
                    return $t;
                }
            }
            
            // If we still haven't found a tenant, log all available tenants for debugging
            Log::debug('No tenant found for subdomain, available tenants:', [
                'subdomain' => $subdomain,
                'tenant_count' => $allTenants->count(),
                'tenants' => $allTenants->map(function($t) {
                    return [
                        'id' => $t->id,
                        'name' => $t->name,
                        'domain' => $t->domain
                    ];
                })
            ]);
            
            return null;
        });
    }
    
    /**
     * Set the current tenant
     * 
     * @param Tenant $tenant
     * @return void
     */
    public function setCurrentTenant(Tenant $tenant)
    {
        $this->currentTenant = $tenant;
        
        // Make the tenant available in the application container
        app()->instance('tenant', $tenant);
        
        // Set the tenant ID in the config for any services that need it
        config(['tenant.id' => $tenant->id]);
    }
    
    /**
     * Get the current tenant
     * 
     * @return Tenant|null
     */
    public function getCurrentTenant()
    {
        return $this->currentTenant;
    }
    
    /**
     * Clear the current tenant context
     * 
     * @return void
     */
    public function clearCurrentTenant()
    {
        $this->currentTenant = null;
        app()->forgetInstance('tenant');
        config(['tenant.id' => null]);
    }
    
    /**
     * Get the default tenant for development
     * 
     * @return Tenant|null
     */
    public function getDefaultTenant()
    {
        return Cache::remember('default_tenant', 60, function () {
            // First try to get the tenant named 'Demo Company'
            $tenant = Tenant::where('name', 'Demo Company')->first();
            
            if ($tenant) {
                return $tenant;
            }
            
            // Otherwise, just get the first active tenant
            return Tenant::where('is_active', true)->first();
        });
    }
    
    /**
     * Check if we should use fallback tenant for this request
     * 
     * @param Request $request
     * @return bool
     */
    private function shouldUseFallbackTenant(Request $request)
    {
        // Only use fallback for authenticated routes that need tenant context
        // Skip for public routes, auth routes, etc.
        $path = $request->path();
        $needsTenant = (
            str_starts_with($path, 'inbox') ||
            str_starts_with($path, 'dashboard') ||
            str_starts_with($path, 'api/tenant') ||
            str_starts_with($path, 'settings')
        );
        
        return $needsTenant;
    }
}
