<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\IpRestriction;
use Illuminate\Support\Facades\Cache;

class IpRestrictionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        
        // Deactivate expired restrictions (run once per minute to avoid overhead)
        $lastCleanup = Cache::get('ip_restriction_cleanup', 0);
        if (now()->timestamp - $lastCleanup > 60) {
            IpRestriction::deactivateExpired();
            Cache::put('ip_restriction_cleanup', now()->timestamp, 60);
        }
        
        // Cache IP restriction check for 5 minutes
        $cacheKey = "ip_restriction_{$ip}";
        $blacklisted = Cache::remember($cacheKey, 300, function () use ($ip) {
            return IpRestriction::active()
                ->where('ip_address', $ip)
                ->exists();
        });
        
        if ($blacklisted) {
            abort(403, __('Access Denied.')); //from this IP address
        }
        return $next($request);
    }
}
