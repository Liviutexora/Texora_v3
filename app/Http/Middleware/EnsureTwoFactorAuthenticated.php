<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    public function handle(Request $request, Closure $next) : Response
    {
        if (! file_exists(base_path('.installed'))) {
            return $next($request);
        }
        
        $user = $request->user();

        // If not logged in, skip
        if (!$user) {
            return $next($request);
        }

        // If 2FA is enabled and not yet verified in this session
        if ($user->two_factor_enabled && session('two_factor_authenticated') === false) {
            // Don't redirect if already on the 2FA page (avoid redirect loop)
            $allowedRoutes = [
                'two-factor.show',
                'two-factor.store',
                'logout',
            ];

            if (!$request->routeIs($allowedRoutes)) {
                return redirect()->route('two-factor.show');
            }
        }

        return $next($request);
    }
}