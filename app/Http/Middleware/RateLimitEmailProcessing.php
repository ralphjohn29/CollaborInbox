<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class RateLimitEmailProcessing
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get tenant ID from the request
        $tenantId = $request->route('tenant') ?? 'default';
        
        // Key for tracking email processing for this tenant
        $key = "email:rate_limit:{$tenantId}";
        
        // Get current count
        $current = Redis::get($key) ?? 0;
        
        // Set maximum emails to process per minute (adjust as needed)
        $maxPerMinute = 100;
        
        if ($current >= $maxPerMinute) {
            return response()->json([
                'error' => 'Email processing rate limit exceeded. Please try again later.'
            ], 429);
        }
        
        // Increment the counter
        Redis::incr($key);
        
        // Set expiry if this is the first request
        if ($current == 0) {
            Redis::expire($key, 60); // Expire after 60 seconds (1 minute)
        }
        
        return $next($request);
    }
} 