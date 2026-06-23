<?php

namespace Tests\Feature;

use App\Jobs\SendBookingCancellationSms;
use App\Jobs\SendBookingConfirmationSms;
use App\Jobs\SendBookingReminderSms;
use App\Jobs\SendBookingRescheduledSms;
use App\Models\NotificationPreference;
use App\Models\Provider;
use App\Models\Service;
use App\Models\SlotReservation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BookingSmsMessageService;
use App\Services\TwilioSmsService;
use App\Support\TenantSmsSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Service $service;

    private Provider $provider;

    private SlotReservation $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::forceCreate([
            'name' => 'Clinic Owner',
            'email' => 'owner-sms@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->tenant = Tenant::create([
            'name' => 'SMS Clinic',
            'slug' => 'sms-clinic',
            'status' => 'active',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'owner_id' => $owner->id,
        ]);

        $this->service = Service::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Consultation',
            'duration' => 30,
            'price' => 50,
            'status' => 'active',
        ]);

        $user = User::forceCreate([
            'name' => 'Dr. SMS',
            'email' => 'dr-sms@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->provider = Provider::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $this->booking = SlotReservation::create([
            'tenant_id' => $this->tenant->id,
            'service_id' => $this->service->id,
            'provider_id' => $this->provider->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+15551234567',
            'date' => now()->addDay(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'confirmed',
            'cancellation_token' => 'sms-test-token',
        ]);
    }

    private function enableTenantSms(array $overrides = []): void
    {
        TenantSmsSettings::for($this->tenant->id)->save(array_merge([
            'sms_enabled' => true,
            'twilio_account_sid' => 'ACtest123',
            'twilio_auth_token' => 'secret-token',
            'twilio_from_number' => '+15559876543',
            'sms_confirmation' => true,
            'sms_reminder' => true,
            'sms_cancellation' => true,
            'sms_rescheduled' => true,
        ], $overrides));
    }

    private function enablePlatformSms(string $permission): void
    {
        $admin = User::forceCreate([
            'name' => 'Super Admin',
            'email' => 'admin'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        NotificationPreference::create([
            'user_id' => $admin->id,
            'permission_name' => $permission,
            'email' => false,
            'sms' => true,
            'web_notification' => false,
        ]);
    }

    private function runJob(object $job): void
    {
        $job->handle(
            app(BookingSmsMessageService::class),
            app(TwilioSmsService::class),
        );
    }

    // ── NotificationPreference::isSmsEnabled ───────────────────────────────

    public function test_sms_disabled_when_no_preference_exists(): void
    {
        $this->assertFalse(NotificationPreference::isSmsEnabled('booking_confirmation'));
    }

    public function test_sms_enabled_when_admin_opted_in(): void
    {
        $this->enablePlatformSms('booking_confirmation');

        $this->assertTrue(NotificationPreference::isSmsEnabled('booking_confirmation'));
    }

    // ── SendBookingConfirmationSms ───────────────────────────────────────────

    public function test_confirmation_sms_skips_when_no_phone(): void
    {
        Http::fake();
        $this->enableTenantSms();
        $this->enablePlatformSms('booking_confirmation');
        $this->booking->phone = null;

        $this->runJob(new SendBookingConfirmationSms($this->booking));

        Http::assertNothingSent();
    }

    public function test_confirmation_sms_skips_when_tenant_sms_disabled(): void
    {
        Http::fake();
        $this->enablePlatformSms('booking_confirmation');

        $this->runJob(new SendBookingConfirmationSms($this->booking));

        Http::assertNothingSent();
    }

    public function test_confirmation_sms_skips_when_platform_sms_disabled(): void
    {
        Http::fake();
        $this->enableTenantSms();

        $this->runJob(new SendBookingConfirmationSms($this->booking));

        Http::assertNothingSent();
    }

    public function test_confirmation_sms_sends_via_twilio_when_enabled(): void
    {
        Http::fake([
            'api.twilio.com/*' => Http::response(['sid' => 'SM123'], 201),
        ]);

        $this->enableTenantSms();
        $this->enablePlatformSms('booking_confirmation');

        $this->runJob(new SendBookingConfirmationSms($this->booking));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.twilio.com')
                && $request['To'] === '+15551234567'
                && $request['From'] === '+15559876543'
                && str_contains($request['Body'], 'confirmed');
        });
    }

    // ── SendBookingReminderSms ─────────────────────────────────────────────

    public function test_reminder_sms_skips_when_already_sent(): void
    {
        Http::fake();
        $this->enableTenantSms();
        $this->enablePlatformSms('booking_reminder');
        $this->booking->update(['sms_reminder_sent_at' => now()->subHour()]);

        $this->runJob(new SendBookingReminderSms($this->booking->fresh()));

        Http::assertNothingSent();
        $this->assertNotNull($this->booking->fresh()->sms_reminder_sent_at);
    }

    public function test_reminder_sms_sets_sms_reminder_sent_at_after_sending(): void
    {
        Http::fake([
            'api.twilio.com/*' => Http::response(['sid' => 'SM456'], 201),
        ]);

        $this->enableTenantSms();
        $this->enablePlatformSms('booking_reminder');

        $this->assertNull($this->booking->fresh()->sms_reminder_sent_at);

        $this->runJob(new SendBookingReminderSms($this->booking));

        $this->assertNotNull($this->booking->fresh()->sms_reminder_sent_at);
    }

    // ── SendBookingCancellationSms ───────────────────────────────────────────

    public function test_cancellation_sms_sends_when_enabled(): void
    {
        Http::fake([
            'api.twilio.com/*' => Http::response(['sid' => 'SM789'], 201),
        ]);

        $this->enableTenantSms();
        $this->enablePlatformSms('booking_cancellation');

        $this->runJob(new SendBookingCancellationSms($this->booking));

        Http::assertSent(fn ($request) => str_contains($request['Body'], 'cancelled'));
    }

    // ── SendBookingRescheduledSms ──────────────────────────────────────────────

    public function test_rescheduled_sms_sends_when_enabled(): void
    {
        Http::fake([
            'api.twilio.com/*' => Http::response(['sid' => 'SM000'], 201),
        ]);

        $this->enableTenantSms();
        $this->enablePlatformSms('booking_rescheduled');

        $this->runJob(new SendBookingRescheduledSms($this->booking));

        Http::assertSent(fn ($request) => str_contains($request['Body'], 'rescheduled'));
    }
}
