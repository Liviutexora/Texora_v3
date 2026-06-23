<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantFromUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! file_exists(base_path('.installed'))) {
            return $next($request);
        }

        $user = auth()->user();

        // Super admin without an active impersonation session has no place on /manage — send to /admin
        if (
            $user &&
            $user->hasRole('super_admin') &&
            ! session('impersonate_tenant_id') &&
            ($request->is('manage') || $request->is('manage/*'))
        ) {
            return redirect('/admin');
        }

        // Super admin impersonation takes priority over the user's own tenant
        $tenantId = session('impersonate_tenant_id')
            ?? $user?->tenant_id
            ?? ($user ? Tenant::where('owner_id', $user->id)->value('id') : null);

        if ($tenantId) {
            $tenant = Tenant::with('plan.activePrices')->find($tenantId);

            if ($tenant) {
                // Billing routes must remain reachable even when the subscription is paused or
                // canceled — it is the only place a tenant can reactivate or resubscribe.
                // All other /manage/* routes stay gated behind isAccessible().
                $isBillingRoute = $request->is('manage/billing')
                    || $request->is('manage/billing/*');

                if ($tenant->isAccessible() || $isBillingRoute) {
                    // Backfill tenant_id on the owner so future requests skip the extra query
                    if ($user && ! $user->tenant_id && ! session('impersonate_tenant_id')) {
                        $user->update(['tenant_id' => $tenant->id]);
                    }

                    TenantContext::set($tenant);
                }
            }
        }

        // Authenticated user with no resolvable tenant trying to access the tenant panel
        // → send them to onboarding rather than letting them see a broken/leaked view
        if (
            $user &&
            ! TenantContext::isSet() &&
            ! $user->hasRole('super_admin') &&
            ($request->is('manage') || $request->is('manage/*')) &&
            ! $request->is('setup') &&
            ! $request->is('setup/*')
        ) {
            return redirect()->route('tenant.setup');
        }

        return $next($request);
    }
}
