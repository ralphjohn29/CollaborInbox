<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $currentTenant = app(TenantManager::class)->getCurrentTenant();

        // If there's no current tenant or no authenticated user, proceed
        if (!$currentTenant || !$user) {
            return $next($request);
        }

        // Check if the user belongs to the current tenant
        if ($user->tenant_id !== $currentTenant->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. You do not have access to this tenant.',
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')->with('error', 'Unauthorized. You do not have access to this tenant.');
        }

        return $next($request);
    }
}
