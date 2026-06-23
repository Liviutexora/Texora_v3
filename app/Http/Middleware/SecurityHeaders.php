<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Embed mode: ?embed=inline or ?embed=popup — allow framing from any origin
        $isEmbed = in_array($request->query('embed'), ['inline', 'popup'], true);

        if ($isEmbed) {
            // Remove X-Frame-Options so browsers don't block the iframe
            $response->headers->remove('X-Frame-Options');
        } else {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Only set HSTS when actually on HTTPS — avoids locking out local HTTP dev
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP: all JS/CSS/fonts are now self-hosted; only reCAPTCHA requires external domains.
        $csp = implode('; ', array_filter([
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "connect-src 'self' wss: https://www.google.com",
            "frame-src 'self' https://www.google.com",
            // Allow embedding from any origin in embed mode; restrict to self otherwise
            $isEmbed ? "frame-ancestors *" : "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self' https://checkout.stripe.com",
        ]));

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
