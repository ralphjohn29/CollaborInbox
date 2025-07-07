<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TenantResolver
{
    /**
     * Extract the subdomain from the request host
     *
     * @param Request $request
     * @return string|null
     */
    public function extractSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $wildcardDomain = config('tenancy.domain.wildcard_domain', '*.collaborinbox.test');
        $baseHost = Str::after($wildcardDomain, '*_');
        
        // If the host doesn't contain the base host, return null
        if (!Str::endsWith($host, $baseHost)) {
            return null;
        }
        
        // Remove the base host part to get the subdomain
        $subdomain = rtrim(Str::beforeLast($host, $baseHost), '.');
        
        // Skip 'www' as it's not a tenant subdomain
        if ($subdomain === 'www') {
            return null;
        }
        
        return $subdomain ?: null;
    }
    
    /**
     * Find a tenant by subdomain
     *
     * @param string $subdomain
     * @return Tenant|null
     */
    public function findTenant(string $subdomain): ?Tenant
    {
        $wildcardDomain = config('tenancy.domain.wildcard_domain', '*.collaborinbox.test');
        $baseHost = Str::after($wildcardDomain, '*_');
        $domain = $subdomain . '.' . $baseHost;
        
        // Find tenant by domain using the domain relationship
        $tenant = Tenant::query()
            ->whereHas('domains', function ($query) use ($domain) {
                $query->where('domain', $domain);
            })
            ->first();
            
        if ($tenant) {
            Log::info("Tenant found for domain {$domain}", ['tenant_id' => $tenant->id]);
        } else {
            Log::info("No tenant found for domain {$domain}");
        }
        
        return $tenant;
    }
    
    /**
     * Find a tenant by the request
     *
     * @param Request $request
     * @return Tenant|null
     */
    public function findTenantByRequest(Request $request): ?Tenant
    {
        $subdomain = $this->extractSubdomain($request);
        
        if (!$subdomain) {
            return null;
        }
        
        return $this->findTenant($subdomain);
    }
    
    /**
     * Resolve a tenant based on arguments
     * Required by the IdentificationMiddleware class 
     *
     * @param mixed ...$args The tenant instance from findTenantByRequest
     * @return Tenant
     * @throws \Exception
     */
    public function resolve(...$args)
    {
        // In our implementation, the first argument is the tenant instance
        if (empty($args) || !$args[0] instanceof Tenant) {
            throw new \Exception("Tenant could not be identified");
        }
        
        return $args[0];
    }
}