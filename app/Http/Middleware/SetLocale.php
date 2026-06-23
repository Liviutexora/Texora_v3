<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    // All languages that have translation files in lang/
    public const SUPPORTED_LOCALES = ['en', 'es', 'de', 'fr', 'ar', 'ru', 'zh', 'hi'];

    public static function enabledLocales(): array
    {
        $stored = Setting::get('enabled_languages');
        if ($stored && trim($stored)) {
            $list = array_values(array_filter(array_map('trim', explode(',', $stored))));
            if (count($list) > 0) {
                return array_values(array_intersect($list, self::SUPPORTED_LOCALES));
            }
        }

        return self::SUPPORTED_LOCALES;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Authenticated user's saved preference (panel users)
        $user = Auth::user();
        if ($user && $user->locale && $this->isSupported($user->locale)) {
            return $user->locale;
        }

        // 2. Resolve tenant — TenantContext is set by IdentifyTenant route middleware
        // which runs AFTER the web group. For public booking pages ({tenant} slug in
        // the URL), we read the slug directly from the route so we don't depend on
        // middleware order.
        $tenant = TenantContext::current();
        if (! $tenant && $request->route('tenant')) {
            $tenant = \App\Models\Tenant::where('slug', $request->route('tenant'))->first();
        }

        // 3. Tenant's default language takes precedence over visitor session so the
        // booking page always opens in the business's configured language by default.
        if ($tenant && $tenant->locale && $this->isSupported($tenant->locale)) {
            // Only use tenant default when visitor has no explicit session override
            // scoped to THIS tenant (prevents cross-tenant leakage).
            $sessionKey = 'locale_' . $tenant->id;
            $tenantSession = $request->session()->get($sessionKey);
            if ($tenantSession && $this->isSupported($tenantSession)) {
                return $tenantSession;
            }
            return $tenant->locale;
        }

        // 4. Generic visitor session/cookie override (non-tenant pages)
        $sessionLocale = $request->session()->get('locale');
        if ($sessionLocale && $this->isSupported($sessionLocale)) {
            return $sessionLocale;
        }

        // 5. App default
        return config('app.locale', 'en');
    }

    private function isSupported(string $locale): bool
    {
        return in_array($locale, self::enabledLocales(), true);
    }
}
