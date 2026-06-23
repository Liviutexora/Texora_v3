<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderSlotOverride;
use App\Models\Service;
use App\Models\SlotReservation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\GoogleCalendarSyncService;
use App\Support\TenantCalendarSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class GoogleCalendarSyncTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Provider $provider;

    private SlotReservation $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::forceCreate([
            'name' => 'Calendar Owner',
            'email' => 'owner-cal@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->tenant = Tenant::create([
            'name' => 'Calendar Clinic',
            'slug' => 'calendar-clinic',
            'status' => 'active',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'owner_id' => $owner->id,
        ]);

        TenantCalendarSettings::for($this->tenant->id)->save([
            'calendar_sync_enabled' => true,
            'calendar_two_way_sync' => true,
        ]);

        $service = Service::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Checkup',
            'duration' => 30,
            'price' => 50,
            'status' => 'active',
        ]);

        $providerUser = User::forceCreate([
            'name' => 'Dr Cal',
            'email' => 'dr-cal@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->provider = Provider::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $providerUser->id,
            'status' => 'active',
            'calendar_sync_enabled' => true,
            'google_access_token' => 'fake-access-token',
            'google_token_expires_at' => now()->addHour(),
            'google_calendar_id' => 'primary',
        ]);

        $this->booking = SlotReservation::create([
            'tenant_id' => $this->tenant->id,
            'service_id' => $service->id,
            'provider_id' => $this->provider->id,
            'name' => 'Jordan',
            'email' => 'jordan@example.com',
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '14:30:00',
            'status' => 'confirmed',
            'amount' => 50,
            'currency' => 'USD',
            'payment_status' => 'paid',
            'cancellation_token' => (string) Str::uuid(),
        ]);
    }

    public function test_outbound_sync_stores_google_event_id(): void
    {
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events' => Http::response([
                'id' => 'gcal_event_abc123',
            ], 200),
        ]);

        app(GoogleCalendarSyncService::class)->syncBookingToCalendar($this->booking);

        $this->booking->refresh();
        $this->assertSame('gcal_event_abc123', $this->booking->google_calendar_event_id);
    }

    public function test_outbound_delete_clears_google_event_id(): void
    {
        $this->booking->update(['google_calendar_event_id' => 'gcal_event_abc123']);

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events/gcal_event_abc123' => Http::response('', 204),
        ]);

        app(GoogleCalendarSyncService::class)->deleteBookingFromCalendar($this->booking);

        $this->booking->refresh();
        $this->assertNull($this->booking->google_calendar_event_id);
    }

    public function test_maybe_delete_calendar_dispatches_job_when_event_exists(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $this->booking->update(['google_calendar_event_id' => 'gcal_event_abc123']);

        app(\App\Services\BookingPaymentService::class)->maybeDeleteCalendar($this->booking);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\DeleteBookingFromGoogleCalendar::class);
    }

    public function test_inbound_sync_creates_blocked_override_for_external_events(): void
    {
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response([
                'items' => [
                    [
                        'id' => 'external_busy_1',
                        'status' => 'confirmed',
                        'summary' => 'Personal appointment',
                        'start' => ['dateTime' => now()->addDay()->setTime(11, 0)->toIso8601String()],
                        'end' => ['dateTime' => now()->addDay()->setTime(12, 0)->toIso8601String()],
                    ],
                ],
            ], 200),
        ]);

        $created = app(GoogleCalendarSyncService::class)->syncBusyFromCalendar($this->provider, 7);

        $this->assertGreaterThanOrEqual(1, $created);

        $this->assertDatabaseHas('provider_slot_overrides', [
            'provider_id' => $this->provider->id,
            'external_event_id' => 'external_busy_1',
        ]);

        $override = ProviderSlotOverride::where('external_event_id', 'external_busy_1')->first();
        $this->assertNotNull($override);
        $this->assertSame('Personal appointment', $override->reason);
    }

    public function test_disconnect_clears_provider_tokens(): void
    {
        $this->provider->update([
            'google_refresh_token' => 'refresh-token',
            'google_token_expires_at' => now()->addHour(),
        ]);

        app(GoogleCalendarSyncService::class)->disconnect($this->provider);

        $this->provider->refresh();
        $this->assertNull($this->provider->google_access_token);
        $this->assertNull($this->provider->google_refresh_token);
        $this->assertFalse($this->provider->calendar_sync_enabled);
    }
}
