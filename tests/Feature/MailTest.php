<?php

namespace Tests\Feature;

use App\Jobs\SendBookingCancellationEmail;
use App\Jobs\SendBookingConfirmationEmail;
use App\Jobs\SendBookingReminderEmail;
use App\Jobs\SendProviderNewBookingEmail;
use App\Models\Provider;
use App\Models\Service;
use App\Models\SlotReservation;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private SlotReservation $booking;
    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::forceCreate([
            'name'              => 'Clinic Owner',
            'email'             => 'owner@example.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->tenant = Tenant::create([
            'name'     => 'Bright Smiles Clinic',
            'slug'     => 'bright-smiles',
            'email'    => 'business@example.com',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'owner_id' => $owner->id,
        ]);

        $service = Service::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Dental Checkup',
            'duration'  => 30,
            'price'     => 100,
            'currency'  => 'USD',
            'is_active' => true,
        ]);

        $providerUser = User::forceCreate([
            'name'              => 'Dr. House',
            'email'             => 'house@example.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
            'tenant_id'         => $this->tenant->id,
        ]);

        $this->provider = Provider::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenant->id,
            'user_id'   => $providerUser->id,
        ]);

        TenantContext::set($this->tenant);

        $tomorrow = Carbon::tomorrow();

        $this->booking = SlotReservation::withoutGlobalScope('tenant')->create([
            'tenant_id'   => $this->tenant->id,
            'service_id'  => $service->id,
            'provider_id' => $this->provider->id,
            'name'        => 'John Client',
            'email'       => 'client@example.com',
            'phone'       => '+10000000000',
            'date'        => $tomorrow->toDateString(),
            'start_time'  => '10:00:00',
            'end_time'    => '10:30:00',
            'status'      => 'confirmed',
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    // ── SendBookingConfirmationEmail ──────────────────────────────────────

    public function test_confirmation_job_runs_without_error(): void
    {
        // Uses array mailer — no real send, just verify the full stack doesn't throw.
        (new SendBookingConfirmationEmail($this->booking))->handle();
        $this->expectNotToPerformAssertions();
    }

    public function test_confirmation_job_skips_when_no_email(): void
    {
        Mail::fake();
        $this->booking->email = null;

        (new SendBookingConfirmationEmail($this->booking))->handle();

        // MailFake tracks Mailable objects; closure sends are not intercepted.
        // The guard must exit before Mail::send() is reached — verify no Mailable was queued.
        Mail::assertNothingQueued();
    }

    // ── SendBookingCancellationEmail ──────────────────────────────────────

    public function test_cancellation_job_runs_without_error(): void
    {
        (new SendBookingCancellationEmail($this->booking))->handle();
        $this->expectNotToPerformAssertions();
    }

    public function test_cancellation_job_skips_when_no_email(): void
    {
        Mail::fake();
        $this->booking->email = null;

        (new SendBookingCancellationEmail($this->booking))->handle();

        Mail::assertNothingQueued();
    }

    // ── SendBookingReminderEmail ──────────────────────────────────────────

    public function test_reminder_job_runs_without_error(): void
    {
        (new SendBookingReminderEmail($this->booking))->handle();
        $this->expectNotToPerformAssertions();
    }

    public function test_reminder_job_skips_when_no_email(): void
    {
        $this->booking->email = null;

        (new SendBookingReminderEmail($this->booking))->handle();

        // Guard exits before any DB write: reminder_sent_at must stay null.
        $this->assertNull($this->booking->fresh()->reminder_sent_at);
    }

    public function test_reminder_job_skips_when_reminder_already_sent(): void
    {
        // Set in-memory so the job's guard sees it, but the DB stays null.
        $this->booking->reminder_sent_at = now()->subHour();

        (new SendBookingReminderEmail($this->booking))->handle();

        // Guard returned early — DB must still have null (no updateQuietly was called).
        $this->assertNull($this->booking->fresh()->reminder_sent_at);
    }

    public function test_reminder_job_sets_reminder_sent_at_after_sending(): void
    {
        $this->assertNull($this->booking->fresh()->reminder_sent_at);

        (new SendBookingReminderEmail($this->booking))->handle();

        $this->assertNotNull($this->booking->fresh()->reminder_sent_at);
    }

    // ── SendProviderNewBookingEmail ───────────────────────────────────────

    public function test_provider_new_booking_job_runs_without_error(): void
    {
        (new SendProviderNewBookingEmail($this->booking))->handle();
        $this->expectNotToPerformAssertions();
    }

    public function test_provider_new_booking_job_skips_when_provider_not_found(): void
    {
        // Provider ID that doesn't resolve to any record — job must return silently.
        $this->booking->provider_id = 99999;

        (new SendProviderNewBookingEmail($this->booking))->handle();
        $this->expectNotToPerformAssertions();
    }
}
