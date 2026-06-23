<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Services\GoogleCalendarSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GoogleCalendarController extends Controller
{
    public function redirect(Request $request, GoogleCalendarSyncService $calendar): RedirectResponse
    {
        $providerId = (int) $request->query('provider_id');
        $provider = Provider::query()->findOrFail($providerId);

        Gate::authorize('update', $provider);

        $url = $calendar->oauthRedirectUrl($provider);
        if (! $url) {
            return redirect()
                ->route('filament.tenant.pages.calendar-settings')
                ->with('error', __('Google Calendar OAuth is not configured. Set GOOGLE_CALENDAR_CLIENT_ID and GOOGLE_CALENDAR_CLIENT_SECRET.'));
        }

        return redirect()->away($url);
    }

    public function callback(Request $request, GoogleCalendarSyncService $calendar): RedirectResponse
    {
        if ($request->query('error')) {
            return redirect()
                ->route('filament.tenant.pages.calendar-settings')
                ->with('error', __('Google Calendar connection was cancelled.'));
        }

        $stateRaw = (string) $request->query('state', '');
        try {
            $state = json_decode(decrypt($stateRaw), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            abort(403);
        }

        $providerId = (int) ($state['provider_id'] ?? 0);
        $provider = Provider::query()->findOrFail($providerId);

        Gate::authorize('update', $provider);

        $redirectUri = route('calendar.oauth.callback');
        $ok = $calendar->handleOAuthCallback((string) $request->query('code'), $redirectUri, $provider);

        if (! $ok) {
            return redirect()
                ->route('filament.tenant.pages.calendar-settings')
                ->with('error', __('Could not connect Google Calendar. Please try again.'));
        }

        return redirect()
            ->route('filament.tenant.pages.calendar-settings')
            ->with('success', __('Google Calendar connected for :name.', ['name' => $provider->user?->name ?? __('provider')]));
    }

    public function disconnect(Request $request, GoogleCalendarSyncService $calendar): RedirectResponse
    {
        $providerId = (int) $request->input('provider_id');
        $provider = Provider::query()->findOrFail($providerId);

        Gate::authorize('update', $provider);

        $calendar->disconnect($provider);

        return redirect()
            ->route('filament.tenant.pages.calendar-settings')
            ->with('success', __('Google Calendar disconnected.'));
    }
}
