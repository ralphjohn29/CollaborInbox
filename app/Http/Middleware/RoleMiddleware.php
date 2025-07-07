<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Check if user has the required role
        if (!$user->hasRole($role)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden: Insufficient role privileges'], 403);
            }
            
            abort(403, 'Forbidden: Insufficient role privileges');
        }

        return $next($request);
    }
}
