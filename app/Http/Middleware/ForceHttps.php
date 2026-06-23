<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     * Redirects HTTP to HTTPS if force_https setting is enabled.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Never redirect on localhost or local Valet/dev TLDs (.test, .local)
        $host = $request->getHost();
        if (in_array($host, ['localhost', '127.0.0.1', '::1'])
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local')) {
            return $next($request);
        }

        // Only apply in production or if explicitly enabled
        if (app()->environment('production') || $this->shouldForceHttps()) {
            if (!$request->secure() && $request->getScheme() !== 'https') {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }

    /**
     * Check if HTTPS should be forced based on settings
     */
    protected function shouldForceHttps(): bool
    {
        try {
            if (file_exists(base_path('.installed'))) {
                $forceHttps = \App\Models\Setting::get('force_https', false);
                return (bool) $forceHttps;
            }
        } catch (\Exception) {
            // If database is not available, return false
        }

        return false;
    }
}

