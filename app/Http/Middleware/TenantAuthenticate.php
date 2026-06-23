<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate;

/**
 * Tenant panel authentication middleware.
 *
 * Identical to Filament's default Authenticate except it redirects
 * unauthenticated users to the shared /login page instead of the
 * panel-specific /manage/login route (which no longer exists).
 */
class TenantAuthenticate extends Authenticate
{
    protected function redirectTo($request): ?string
    {
        return route('login');
    }
}
