<?php

namespace Tests\Feature;

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
use Livewire\Livewire;
use Tests\TestCase;

class ClassicThemeTest extends TestCase
{
    use RefreshDatabase;

    private Tenant   $tenant;
    private Service  $service;
    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::forceCreate([
            'name'              => 'Theme Owner',
            'email'             => 'themeowner@example.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->tenant = Tenant::create([
            'name'     => 'Theme Studio',
            'slug'     => 'theme-studio',
            'email'    => 'theme@example.com',
            'timezone' => 'America/New_York',
            'currency' => 'USD',
            'owner_id' => $owner->id,
        ]);

        \DB::table('users')->where('id', $owner->id)->update(['tenant_id' => $this->tenant->id]);

        $this->service = Service::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Haircut',
            'duration'  => 60,
            'price'     => 40,
            'currency'  => 'USD',
            'is_active' => true,
        ]);

        $providerUser = User::forceCreate([
            'name'              => 'Stylist One',
            'email'             => 'stylist@example.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
            'tenant_id'         => $this->tenant->id,
        ]);

        $this->provider = Provider::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenant->id,
            'user_id'   => $providerUser->id,
        ]);

        $this->provider->services()->attach($this->service->id);
        TenantContext::set($this->tenant);
    }

    /** Drives the wizard to step 6 (success screen) and returns the component. */
    private function atSuccessScreen(string $date = null, string $start = '10:00', string $end = '11:00'): \Livewire\Features\SupportTesting\Testable
    {
        Queue::fake();

        $date ??= Carbon::tomorrow()->toDateString();

        return Livewire::test(BookingWizard::class)
            ->set('serviceId',     $this->service->id)
            ->set('providerId',    $this->provider->id)
            ->set('selectedDate',  $date)
            ->set('selectedStart', $start)
            ->set('selectedEnd',   $end)
            ->set('customFields', [
                ['id' => 'f1', 'type' => 'email',      'label' => 'Email', 'required' => true,  'placeholder' => ''],
                ['id' => 'f2', 'type' => 'short_text', 'label' => 'Name',  'required' => true,  'placeholder' => ''],
            ])
            ->set('customAnswers', ['cal@example.com', 'Cal User'])
            ->call('confirm');
    }

    // ── CP-A: Google Calendar URL ─────────────────────────────────────

    public function test_success_screen_google_button_uses_google_calendar_url(): void
    {
        $component = $this->atSuccessScreen('2026-06-15', '10:00', '11:00');

        $component->assertSee('calendar.google.com');
        $component->assertSee('action=TEMPLATE');
        $component->assertSee('20260615T100000');
    }

    public function test_success_screen_apple_button_uses_ical_download(): void
    {
        $component = $this->atSuccessScreen();

        $html = $component->html();
        // Apple button must contain the .ical route, not calendar.google.com
        $this->assertStringContainsString('booking/', $html);
        $this->assertStringContainsString('ical', $html);
    }

    public function test_success_screen_google_url_contains_correct_timezone(): void
    {
        $component = $this->atSuccessScreen('2026-06-15', '09:00', '10:00');

        $component->assertSee('America%2FNew_York');
    }

    public function test_success_screen_null_selected_end_falls_back_to_start_time(): void
    {
        Queue::fake();

        // Drive to success with selectedEnd null (edge case)
        $date = Carbon::tomorrow()->toDateString();

        $component = Livewire::test(BookingWizard::class)
            ->set('serviceId',     $this->service->id)
            ->set('providerId',    $this->provider->id)
            ->set('selectedDate',  $date)
            ->set('selectedStart', '14:00')
            ->set('selectedEnd',   null) // force null
            ->set('customFields', [
                ['id' => 'f1', 'type' => 'email',      'label' => 'Email', 'required' => true,  'placeholder' => ''],
                ['id' => 'f2', 'type' => 'short_text', 'label' => 'Name',  'required' => true,  'placeholder' => ''],
            ])
            ->set('customAnswers', ['edge@example.com', 'Edge User'])
            ->call('confirm');

        // Should reach step 6 without error and Google Calendar URL should not be malformed
        $component->assertSet('step', 6);
        // URL must not contain an empty end date (dates=XXXXXTZ/ with nothing after slash)
        $html = $component->html();
        $this->assertStringNotContainsString('dates=/', $html);
    }
}
