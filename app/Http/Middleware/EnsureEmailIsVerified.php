<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!file_exists(base_path('.installed'))) {
            return $next($request);
        }
        // Check if email verification is required
        $requireEmailVerification = Setting::get('require_email_verification', false);
        
        if ($requireEmailVerification && Auth::check()) {
            $user = Auth::user();
            
            // If email verification is required and user hasn't verified their email
            if (!$user->hasVerifiedEmail()) {
                // Allow access to verification routes, logout, and auth routes
                $allowedRoutes = [
                    'verification.*',
                    'verification.notice',
                    'verification.verify',
                    'verification.send',
                    'logout',
                    'password.*',
                    'password.request',
                    'password.reset',
                    'password.update',
                    'password.confirm',
                ];
                
                foreach ($allowedRoutes as $route) {
                    if ($request->routeIs($route)) {
                        return $next($request);
                    }
                }
                
                // Redirect to email verification notice page
                return redirect()->route('verification.notice');
            }
        }
        
        return $next($request);
    }
}

