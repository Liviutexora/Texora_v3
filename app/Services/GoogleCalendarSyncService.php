<?php

namespace App\Services;

use App\Enums\SlotOverrideStatus;
use App\Models\Provider;
use App\Models\ProviderSlotOverride;
use App\Models\SlotReservation;
use App\Support\TenantCalendarSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarSyncService
{
    private const CALENDAR_SCOPE = 'https://www.googleapis.com/auth/calendar';

    public function syncBookingToCalendar(SlotReservation $booking): void
    {
        $settings = TenantCalendarSettings::for($booking->tenant_id);

        if (! $settings->isEnabled()) {
            return;
        }

        $provider = $booking->providerRelation;
        if (! $provider || ! $provider->calendar_sync_enabled || ! $provider->google_access_token) {
            return;
        }

        $token = $this->ensureAccessToken($provider);
        if (! $token) {
            return;
        }

        $calendarId = $provider->google_calendar_id ?: 'primary';
        $timezone = $booking->tenant?->timezone ?? config('app.timezone');

        $start = Carbon::parse("{$booking->date->format('Y-m-d')} {$booking->start_time}", $timezone);
        $end = Carbon::parse("{$booking->date->format('Y-m-d')} {$booking->end_time}", $timezone);

        $eventBody = [
            'summary'     => ($booking->service?->name ?? __('Booking')) . ' — ' . $booking->name,
            'description' => trim(implode("\n", array_filter([
                $booking->email,
                $booking->phone,
                $booking->note,
            ]))),
            'start' => [
                'dateTime' => $start->toIso8601String(),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end->toIso8601String(),
                'timeZone' => $timezone,
            ],
        ];

        try {
            if ($booking->google_calendar_event_id) {
                $response = Http::withToken($token)
                    ->put("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$booking->google_calendar_event_id}", $eventBody);
            } else {
                $response = Http::withToken($token)
                    ->post("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events", $eventBody);
            }

            if ($response->successful()) {
                $eventId = $response->json('id');
                if ($eventId && $booking->google_calendar_event_id !== $eventId) {
                    $booking->update(['google_calendar_event_id' => $eventId]);
                }
            } else {
                Log::warning('GoogleCalendarSyncService: event sync failed', [
                    'booking_id' => $booking->id,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('GoogleCalendarSyncService: exception on outbound sync', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    public function deleteBookingFromCalendar(SlotReservation $booking): void
    {
        if (! $booking->google_calendar_event_id) {
            return;
        }

        $settings = TenantCalendarSettings::for($booking->tenant_id);

        if (! $settings->isEnabled()) {
            return;
        }

        $provider = $booking->providerRelation;
        if (! $provider || ! $provider->calendar_sync_enabled || ! $provider->google_access_token) {
            return;
        }

        $token = $this->ensureAccessToken($provider);
        if (! $token) {
            return;
        }

        $calendarId = $provider->google_calendar_id ?: 'primary';
        $eventId = $booking->google_calendar_event_id;

        try {
            $response = Http::withToken($token)
                ->delete("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$eventId}");

            if ($response->successful() || $response->status() === 410) {
                $booking->update(['google_calendar_event_id' => null]);
            } else {
                Log::warning('GoogleCalendarSyncService: event delete failed', [
                    'booking_id' => $booking->id,
                    'event_id'   => $eventId,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('GoogleCalendarSyncService: exception on event delete', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    public function syncBusyFromCalendar(Provider $provider, int $days = 30): int
    {
        $settings = TenantCalendarSettings::for($provider->tenant_id);

        if (! $settings->isEnabled() || ! $settings->twoWaySync()) {
            return 0;
        }

        if (! $provider->calendar_sync_enabled || ! $provider->google_access_token) {
            return 0;
        }

        $token = $this->ensureAccessToken($provider);
        if (! $token) {
            return 0;
        }

        $days = max(1, $days);
        $calendarId = $provider->google_calendar_id ?: 'primary';
        $timeMin = now()->startOfDay()->toIso8601String();
        $timeMax = now()->addDays($days)->endOfDay()->toIso8601String();

        try {
            $response = Http::withToken($token)->get(
                "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events",
                [
                    'timeMin'      => $timeMin,
                    'timeMax'      => $timeMax,
                    'singleEvents' => 'true',
                    'orderBy'      => 'startTime',
                ]
            );

            if (! $response->successful()) {
                return 0;
            }

            $created = 0;
            foreach ($response->json('items', []) as $event) {
                if (($event['status'] ?? '') === 'cancelled') {
                    continue;
                }

                $eventId = $event['id'] ?? null;
                if (! $eventId) {
                    continue;
                }

                // Skip events we created from bookings
                $linkedBooking = SlotReservation::withoutGlobalScope('tenant')
                    ->where('provider_id', $provider->id)
                    ->where('google_calendar_event_id', $eventId)
                    ->exists();

                if ($linkedBooking) {
                    continue;
                }

                $start = $event['start']['dateTime'] ?? ($event['start']['date'] ?? null);
                $end = $event['end']['dateTime'] ?? ($event['end']['date'] ?? null);

                if (! $start || ! $end) {
                    continue;
                }

                $startAt = Carbon::parse($start);
                $endAt = Carbon::parse($end);

                $exists = ProviderSlotOverride::query()
                    ->where('provider_id', $provider->id)
                    ->where('external_event_id', $eventId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                ProviderSlotOverride::create([
                    'tenant_id'         => $provider->tenant_id,
                    'provider_id'       => $provider->id,
                    'date'              => $startAt->toDateString(),
                    'start_time'        => $startAt->format('H:i:s'),
                    'end_time'          => $endAt->format('H:i:s'),
                    'status'            => SlotOverrideStatus::Blocked,
                    'reason'            => $event['summary'] ?? __('Busy (Google Calendar)'),
                    'external_event_id' => $eventId,
                ]);

                $created++;
            }

            return $created;
        } catch (\Throwable $e) {
            Log::error('GoogleCalendarSyncService: inbound sync failed', [
                'provider_id' => $provider->id,
                'error'       => $e->getMessage(),
            ]);

            return 0;
        }
    }

    public function ensureAccessToken(Provider $provider): ?string
    {
        if (! $provider->google_access_token) {
            return null;
        }

        if ($provider->google_token_expires_at && $provider->google_token_expires_at->isFuture()) {
            try {
                return decrypt($provider->google_access_token);
            } catch (\Throwable) {
                return $provider->google_access_token;
            }
        }

        if (! $provider->google_refresh_token) {
            return null;
        }

        $refresh = $provider->google_refresh_token;
        try {
            $refresh = decrypt($provider->google_refresh_token);
        } catch (\Throwable) {
            // stored plain in tests
        }

        $settings = TenantCalendarSettings::for($provider->tenant_id);
        $clientId = $settings->clientId();
        $clientSecret = $settings->clientSecret();

        if (! $clientId || ! $clientSecret) {
            return null;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refresh,
            'grant_type'    => 'refresh_token',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $accessToken = $response->json('access_token');
        $expiresIn = (int) ($response->json('expires_in') ?? 3600);

        $provider->update([
            'google_access_token'    => encrypt($accessToken),
            'google_token_expires_at' => now()->addSeconds($expiresIn - 60),
        ]);

        return $accessToken;
    }

    public function authorizationUrl(Provider $provider, string $redirectUri): ?string
    {
        $settings = TenantCalendarSettings::for($provider->tenant_id);
        $clientId = $settings->clientId();

        if (! $clientId) {
            return null;
        }

        $params = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => self::CALENDAR_SCOPE,
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => encrypt(json_encode([
                'provider_id' => $provider->id,
                'tenant_id'   => $provider->tenant_id,
            ])),
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    }

    public function handleOAuthCallback(string $code, string $redirectUri, Provider $provider): bool
    {
        $settings = TenantCalendarSettings::for($provider->tenant_id);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => $settings->clientId(),
            'client_secret' => $settings->clientSecret(),
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ]);

        if (! $response->successful()) {
            return false;
        }

        $accessToken = $response->json('access_token');
        $refreshToken = $response->json('refresh_token');
        $expiresIn = (int) ($response->json('expires_in') ?? 3600);

        $provider->update([
            'google_access_token'     => encrypt($accessToken),
            'google_refresh_token'    => $refreshToken ? encrypt($refreshToken) : $provider->google_refresh_token,
            'google_token_expires_at' => now()->addSeconds($expiresIn - 60),
            'calendar_sync_enabled'   => true,
        ]);

        return true;
    }

    public function oauthRedirectUrl(Provider $provider): ?string
    {
        $redirectUri = route('calendar.oauth.callback');

        return $this->authorizationUrl($provider, $redirectUri);
    }

    public function disconnect(Provider $provider): void
    {
        $provider->update([
            'calendar_sync_enabled'   => false,
            'google_access_token'     => null,
            'google_refresh_token'    => null,
            'google_token_expires_at' => null,
            'google_calendar_id'      => null,
        ]);
    }
}
