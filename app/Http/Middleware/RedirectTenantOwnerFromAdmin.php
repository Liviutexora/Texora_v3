<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * If a tenant_owner (business owner) somehow lands on the /admin panel,
 * redirect them to their own /manage panel instead.
 * Super admins are unaffected.
 */
class RedirectTenantOwnerFromAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (
            $user &&
            ! $user->hasRole('super_admin') &&
            $user->hasRole('tenant_owner')
        ) {
            return redirect('/manage');
        }

        return $next($request);
    }
}
