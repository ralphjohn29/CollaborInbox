<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerifyTenantAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $tenantIdParam
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $tenantIdParam = 'tenantId'): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return new Response('Unauthorized', 403);
        }
        
        // Extract tenant ID from the request parameters
        $tenantId = $request->route($tenantIdParam);
        
        // Check if the user has access to the tenant
        if (!$user->tenants->contains('id', $tenantId)) {
            return new Response('Forbidden: You do not have access to this tenant', 403);
        }
        
        return $next($request);
    }
} 