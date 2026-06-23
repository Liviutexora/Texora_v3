<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoveIndexPhpFromUrls
{
    /**
     * Handle an incoming request.
     * Redirects URLs containing index.php to clean URLs.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $uri = $request->getRequestUri();
        
        // If the URI contains /index.php, redirect to clean URL
        if (str_contains($uri, '/index.php')) {
            $cleanUri = str_replace('/index.php', '', $uri);
            // Preserve query string if present
            if ($request->getQueryString()) {
                $cleanUri .= '?' . $request->getQueryString();
            }
            return redirect($cleanUri, 301);
        }
        
        return $next($request);
    }
}

