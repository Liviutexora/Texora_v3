<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocaleSwitchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');

        if (! in_array($locale, SetLocale::enabledLocales(), true)) {
            return back();
        }

        // Store tenant-scoped key so visitor overrides don't leak across tenants
        // and the tenant's default language is still respected on first visit.
        $tenant = \App\Support\TenantContext::current();
        if ($tenant) {
            $request->session()->put('locale_' . $tenant->id, $locale);
        } else {
            $request->session()->put('locale', $locale);
        }

        // Persist to DB for authenticated users so it survives session expiry
        if ($user = Auth::user()) {
            $user->update(['locale' => $locale]);
        }

        return back()->withHeaders([
            'Content-Language' => $locale,
        ]);
    }
}
