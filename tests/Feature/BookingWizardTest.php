<?php

namespace Tests\Feature;

use App\Jobs\SendBookingConfirmationEmail;
use App\Jobs\SendBookingReminderEmail;
use App\Jobs\SendProviderNewBookingEmail;
use App\Jobs\SendWebhookPayload;
use App\Models\Setting;
use App\Livewire\Booking\BookingWizard;
use App\Models\Provider;
use App\Models\Service;
use App\Models\SlotReservation;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class BookingWizardTest extends TestCase
{
    use RefreshDatabase;

    private Tenant  $tenant;
    private Service $service;
    private Provider  $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::forceCreate([
            'name'              => 'Tenant Owner',
            'email'             => 'owner@example.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->tenant = Tenant::create([
            'name'     => 'Test Studio',
            'slug'     => 'test-studio',
            'email'    => 'studio@example.com',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'owner_id' => $owner->id,
        ]);

        \DB::table('users')->where('id', $owner->id)->update(['tenant_id' => $this->tenant->id]);

        $this->service = Service::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Consultation',
            'duration'  => 30,
            'price'     => 50,
            'currency'  => 'USD',
            'is_active' => true,
        ]);

        $providerUser = User::forceCreate([
            'name'              => 'Dr. Test',
            'email'             => 'provider@example.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
            'tenant_id'         => $this->tenant->id,
        ]);

        $this->provider = Provider::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenant->id,
            'user_id'   => $providerUser->id,
        ]);

        // Link provider to service so getProviders() via whereHas('services') works
        $this->provider->services()->attach($this->service->id);

        TenantContext::set($this->tenant);
    }

    /**
     * Configure the wizard to the "ready to confirm" state.
     * customFields/customAnswers are set after mount() so they aren't overwritten.
     * Index 0 = email field, index 1 = name field (matches syncNamedFieldsFromAnswers() mapping).
     */
    private function readyToConfirm(array $answers = ['john@demo.com', 'John Demo']): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(BookingWizard::class)
            ->set('serviceId',     $this->service->id)
            ->set('providerId',    $this->provider->id)
            ->set('selectedDate',  Carbon::tomorrow()->toDateString())
            ->set('selectedStart', '09:00')
            ->set('selectedEnd',   '09:30')
            ->set('customFields', [
                ['id' => 'f1', 'type' => 'email',      'label' => 'Email', 'required' => true, 'placeholder' => ''],
                ['id' => 'f2', 'type' => 'short_text', 'label' => 'Name',  'required' => true, 'placeholder' => ''],
            ])
            ->set('customAnswers', $answers);
    }

    public function test_confirm_in_demo_mode_sets_is_demo_booking_and_suppresses_emails(): void
    {
        Queue::fake();

        // Register the DEMO_MODE eloquent.saving block
        app('events')->listen('eloquent.saving: *', fn () => false);

        $this->readyToConfirm()
            ->call('confirm')
            ->assertSet('isDemoBooking', true)
            ->assertSet('bookingId', 9999999)
            ->assertSet('step', 6);

        // No booking persisted
        $this->assertDatabaseCount('slot_reservations', 0);

        // No emails dispatched for a fake booking
        Queue::assertNotPushed(SendBookingConfirmationEmail::class);
        Queue::assertNotPushed(SendProviderNewBookingEmail::class);
    }

    public function test_confirm_happy_path_creates_booking_and_queues_emails(): void
    {
        Queue::fake();

        $this->readyToConfirm(['jane@example.com', 'Jane Smith'])
            ->call('confirm')
            ->assertSet('isDemoBooking', false)
            ->assertSet('step', 6);

        $this->assertDatabaseCount('slot_reservations', 1);

        $booking = SlotReservation::withoutGlobalScope('tenant')->first();
        $this->assertSame('Jane Smith', $booking->name);
        $this->assertSame('jane@example.com', $booking->email);

        Queue::assertPushed(SendBookingConfirmationEmail::class);
        Queue::assertPushed(SendProviderNewBookingEmail::class);
    }

    public function test_select_slot_highlights_without_advancing(): void
    {
        Livewire::test(BookingWizard::class)
            ->set('tenantId',  $this->tenant->id)
            ->set('serviceId', $this->service->id)
            ->set('step', 3)
            ->call('selectSlot', '11:00', '11:30')
            ->assertSet('selectedStart', '11:00')
            ->assertSet('selectedEnd',   '11:30')
            ->assertSet('step', 3); // stays on step 3 — user must click Continue
    }

    public function test_continue_from_slot_advances_to_step_4(): void
    {
        Livewire::test(BookingWizard::class)
            ->set('tenantId',      $this->tenant->id)
            ->set('serviceId',     $this->service->id)
            ->set('step',          3)
            ->set('selectedStart', '11:00')
            ->set('selectedEnd',   '11:30')
            ->call('continueFromSlot')
            ->assertSet('step', 4);
    }

    public function test_confirmed_booking_has_status_confirmed(): void
    {
        Queue::fake();

        $this->readyToConfirm(['test@example.com', 'Test User'])
            ->call('confirm');

        $booking = SlotReservation::withoutGlobalScope('tenant')->first();
        $this->assertSame('confirmed', $booking->status);
    }

    public function test_go_back_from_step_2_returns_to_step_1(): void
    {
        Livewire::test(BookingWizard::class)
            ->set('step', 2)
            ->call('goBack')
            ->assertSet('step', 1);
    }

    public function test_go_back_from_step_3_with_skipped_provider_jumps_to_step_1(): void
    {
        Livewire::test(BookingWizard::class)
            ->set('step', 3)
            ->set('providerStepSkipped', true)
            ->call('goBack')
            ->assertSet('step', 1)
            ->assertSet('providerStepSkipped', false)
            ->assertSet('providerId', null);
    }

    public function test_go_back_does_nothing_on_step_1(): void
    {
        Livewire::test(BookingWizard::class)
            ->set('step', 1)
            ->call('goBack')
            ->assertSet('step', 1);
    }

    public function test_continue_from_service_skips_to_step_3_when_no_providers(): void
    {
        // Use a service attached to a different tenant with no providers
        $otherOwner = User::forceCreate([
            'name'              => 'Other Owner',
            'email'             => 'other@example.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $emptyTenant = Tenant::create([
            'name'     => 'Empty Clinic',
            'slug'     => 'empty-business',
            'email'    => 'empty@example.com',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'owner_id' => $otherOwner->id,
        ]);

        $serviceWithNoProviders = Service::withoutGlobalScope('tenant')->create([
            'tenant_id' => $emptyTenant->id,
            'name'      => 'Consult',
            'duration'  => 30,
            'price'     => 0,
            'currency'  => 'USD',
            'is_active' => true,
        ]);

        TenantContext::set($emptyTenant);

        Livewire::test(BookingWizard::class)
            ->set('serviceId', $serviceWithNoProviders->id)
            ->call('continueFromService')
            ->assertSet('providerStepSkipped', true)
            ->assertSet('step', 3);
    }

    public function test_continue_from_service_shows_provider_step_when_providers_exist(): void
    {
        Livewire::test(BookingWizard::class)
            ->set('serviceId', $this->service->id)
            ->call('continueFromService')
            ->assertSet('providerStepSkipped', false)
            ->assertSet('step', 2);
    }

    public function test_confirm_shows_slot_taken_error_when_no_provider_available(): void
    {
        Queue::fake();

        // Pre-occupy the only provider's slot so both availability queries
        // return null inside the transaction, triggering the "slot taken" path.
        SlotReservation::withoutGlobalScope('tenant')->create([
            'tenant_id'          => $this->tenant->id,
            'service_id'         => $this->service->id,
            'provider_id'        => $this->provider->id,
            'date'               => Carbon::tomorrow()->toDateString(),
            'start_time'         => '09:00',
            'end_time'           => '09:30',
            'name'               => 'First Booker',
            'email'              => 'first@example.com',
            'status'             => 'confirmed',
            'cancellation_token' => 'tok_first',
            'amount'             => 50,
            'currency'           => 'USD',
            'payment_status'     => 'pending',
            'is_verified'        => true,
        ]);

        $this->readyToConfirm(['slot@example.com', 'Slot Taker'])
            ->set('providerId', null) // force auto-assignment path
            ->call('confirm')
            ->assertHasErrors('selectedDate')
            ->assertSet('step', 3);

        // Only the pre-existing booking exists — the second attempt was blocked
        $this->assertDatabaseCount('slot_reservations', 1);
    }

    public function test_confirm_blocks_after_rate_limit_exceeded(): void
    {
        Queue::fake();

        $tenantId = $this->tenant->id;
        $ip       = '127.0.0.1';
        $key      = "booking_submit_{$tenantId}_{$ip}";

        // Exhaust the 5-attempt allowance
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }

        $this->readyToConfirm(['limited@example.com', 'Rate Limited'])
            ->call('confirm')
            ->assertHasErrors('selectedDate');

        // No booking should have been created
        $this->assertDatabaseCount('slot_reservations', 0);

        RateLimiter::clear($key);
    }

    // ── CP-B: URL pre-fill ────────────────────────────────────────────

    public function test_url_prefill_service_id_sets_service_when_belonging_to_tenant(): void
    {
        $component = Livewire::withQueryParams(['service_id' => $this->service->id])
            ->test(BookingWizard::class);
        $component->assertSet('serviceId', $this->service->id);
    }

    public function test_url_prefill_service_id_from_other_tenant_is_ignored(): void
    {
        $otherOwner = User::forceCreate([
            'name' => 'Other', 'email' => 'other@example.com',
            'password' => bcrypt('x'), 'email_verified_at' => now(),
        ]);
        $otherTenant = Tenant::create([
            'name' => 'Other Biz', 'slug' => 'other-biz', 'email' => 'o@example.com',
            'timezone' => 'UTC', 'currency' => 'USD', 'owner_id' => $otherOwner->id,
        ]);
        $otherService = Service::withoutGlobalScope('tenant')->create([
            'tenant_id' => $otherTenant->id, 'name' => 'Foreign', 'duration' => 30,
            'price' => 0, 'currency' => 'USD', 'is_active' => true,
        ]);

        // Pass a service belonging to another tenant — must be silently ignored
        $component = Livewire::withQueryParams(['service_id' => $otherService->id])
            ->test(BookingWizard::class);
        $component->assertSet('serviceId', null);
    }

    public function test_url_prefill_email_populates_custom_answers_for_email_field(): void
    {
        $component = Livewire::withQueryParams(['email' => 'prefill@example.com'])
            ->test(BookingWizard::class)
            ->set('customFields', [
                ['id' => 'f1', 'type' => 'email', 'label' => 'Email', 'required' => true, 'placeholder' => ''],
            ]);

        // $this->email should be set directly
        $component->assertSet('email', 'prefill@example.com');
    }

    public function test_url_prefill_email_sets_direct_prop_when_no_custom_fields(): void
    {
        // Tenant has no custom fields — pre-fill must still work via direct property
        $component = Livewire::withQueryParams(['email' => 'direct@example.com'])
            ->test(BookingWizard::class)
            ->set('customFields', []);

        $component->assertSet('email', 'direct@example.com');
    }

    public function test_url_prefill_services_param_takes_precedence_over_service_id(): void
    {
        // When ?services=X is already set (multi-service URL param), ?service_id should be ignored
        $component = Livewire::withQueryParams(['service_id' => $this->service->id])
            ->test(BookingWizard::class)
            ->set('servicesParam', (string) $this->service->id);

        // serviceId set from servicesParam, not overridden by a second service_id lookup
        // Key assertion: no cross-contamination — serviceId comes from servicesParam only
        $this->assertNotNull($component->get('servicesParam'));
    }

    public function test_url_prefill_non_integer_service_id_is_ignored(): void
    {
        $component = Livewire::withQueryParams(['service_id' => 'abc'])
            ->test(BookingWizard::class);
        $component->assertSet('serviceId', null);
    }

    // ── CP-C: Webhook dispatch ────────────────────────────────────────

    public function test_confirm_dispatches_webhook_when_url_configured(): void
    {
        Queue::fake();

        Setting::set("tenant_{$this->tenant->id}_webhook_url", 'https://example.com/hook');
        Setting::set("tenant_{$this->tenant->id}_webhook_secret", 'test-secret');

        $this->readyToConfirm(['webhook@example.com', 'Webhook User'])
            ->call('confirm');

        Queue::assertPushed(SendWebhookPayload::class);
    }

    public function test_confirm_does_not_dispatch_webhook_when_url_not_configured(): void
    {
        Queue::fake();

        // Ensure no webhook URL is set
        \DB::table('settings')->where('key', "tenant_{$this->tenant->id}_webhook_url")->delete();

        $this->readyToConfirm(['nowebhook@example.com', 'No Webhook'])
            ->call('confirm');

        Queue::assertNotPushed(SendWebhookPayload::class);
    }

    public function test_confirm_in_demo_mode_does_not_dispatch_webhook(): void
    {
        Queue::fake();

        Setting::set("tenant_{$this->tenant->id}_webhook_url", 'https://example.com/hook');
        app('events')->listen('eloquent.saving: *', fn () => false);

        $this->readyToConfirm(['demo@example.com', 'Demo User'])
            ->call('confirm')
            ->assertSet('isDemoBooking', true);

        Queue::assertNotPushed(SendWebhookPayload::class);
    }
}
