<?php

namespace App\Livewire\Tenant;

use App\Models\Provider;
use App\Models\ProviderShift;
use App\Models\Service;
use App\Models\Tenant;
use App\Support\LocalisationOptions;
use App\Support\TenantContext;
use Illuminate\Support\Str;
use Livewire\Component;

class OnboardingWizard extends Component
{
    public int $step = 1;

    // Step 1 — business profile
    public string $businessName = '';
    public string $slug         = '';
    public string $tagline      = '';
    public string $timezone     = '';
    public string $currency     = '';

    public function mount(): void
    {
        $timezones  = LocalisationOptions::timezones();
        $currencies = LocalisationOptions::currencies();

        if ($this->timezone === '' && ! empty($timezones)) {
            $this->timezone = $timezones[0];
        }

        if ($this->currency === '' && ! empty($currencies)) {
            $this->currency = (string) array_key_first($currencies);
        }
    }

    // Step 2 — first service
    public string $serviceName     = '';
    public int    $serviceDuration = 30;
    public string $servicePrice    = '0';
    public string $serviceColor    = '#7c3aed';

    // Step 3 — first provider name (uses existing auth user)
    public string $jobTitle = '';

    // Step 4 — working hours
    public string $shiftStart = '09:00';
    public int    $shiftSlots = 8;
    public array  $shiftDays  = [1, 2, 3, 4, 5];

    // Result
    public ?int $tenantId = null;

    public function updatedBusinessName(string $value): void
    {
        if ($this->step === 1) {
            $this->slug = Str::slug($value);
            $this->validateOnly('slug', [
                'slug' => 'required|alpha_dash|max:100|unique:tenants,slug',
            ]);
        }
    }

    public function updatedSlug(): void
    {
        $this->validateOnly('slug', [
            'slug' => 'required|alpha_dash|max:100|unique:tenants,slug',
        ]);
    }

    // ── step 1 ──────────────────────────────────────────────────────
    public function completeStep1(): void
    {
        $this->validate([
            'businessName' => 'required|string|max:255',
            'slug'         => 'required|alpha_dash|max:100|unique:tenants,slug',
            'timezone'     => 'required|timezone',
            'currency'     => 'required|string|min:2|max:5',
        ], [
            'timezone.required' => 'Please select a timezone.',
            'timezone.timezone' => 'Please select a valid timezone.',
            'currency.required' => 'Please select a currency.',
        ]);

        $this->step = 2;
    }

    // ── step 2 ──────────────────────────────────────────────────────
    public function completeStep2(): void
    {
        $this->validate([
            'serviceName'     => 'required|string|max:255',
            'serviceDuration' => 'required|integer|min:5|max:480',
            'servicePrice'    => 'required|numeric|min:0',
        ]);

        $this->step = 3;
    }

    // ── step 3 ──────────────────────────────────────────────────────
    public function completeStep3(): void
    {
        $this->step = 4;
    }

    // ── step 4 — finish ─────────────────────────────────────────────
    public function finish(): void
    {
        $this->validate([
            'shiftStart' => 'required',
            'shiftSlots' => 'required|integer|min:1|max:50',
            'shiftDays'  => 'required|array|min:1',
        ]);

        $user = auth()->user();

        // 1. Create tenant (plan_id + Stripe IDs come from signup session)
        $stripeSubStatus = session('signup_stripe_sub_status');
        $tenant = Tenant::create([
            'name'                        => $this->businessName,
            'slug'                        => $this->slug,
            'owner_id'                    => $user->id,
            'plan_id'                     => session('signup_plan_id'),
            'status'                      => 'active',
            'trial_ends_at'               => null,
            'stripe_customer_id'          => session('signup_stripe_customer_id'),
            'stripe_subscription_id'      => session('signup_stripe_subscription_id'),
            'stripe_subscription_status'  => $stripeSubStatus,
            'timezone'                    => $this->timezone,
            'currency'                    => $this->currency,
            'booking_page_tagline'        => $this->tagline ?: null,
        ]);

        // 2. Assign user to tenant
        $user->update(['tenant_id' => $tenant->id]);

        // Clear all signup session keys
        session()->forget([
            'signup_plan_id',
            'signup_stripe_customer_id',
            'signup_stripe_subscription_id',
            'signup_stripe_sub_status',
        ]);

        // 3. Set context so subsequent creates auto-fill tenant_id
        TenantContext::set($tenant);

        // 4. Create service
        $service = Service::create([
            'tenant_id'        => $tenant->id,
            'name'             => $this->serviceName,
            'duration_minutes' => $this->serviceDuration,
            'price'            => $this->servicePrice,
            'currency'         => $this->currency,
            'color'            => $this->serviceColor,
            'is_active'        => true,
        ]);

        // 5. Create provider linked to auth user
        $provider = Provider::create([
            'tenant_id' => $tenant->id,
            'user_id'   => $user->id,
            'job_title' => $this->jobTitle ?: null,
        ]);

        // 6. Link provider to service
        $provider->services()->attach($service->id);

        // 7. Create default shift
        ProviderShift::create([
            'tenant_id'            => $tenant->id,
            'provider_id'            => $provider->id,
            'name'                 => __('Default Shift'),
            'start_time'           => $this->shiftStart,
            'slot_duration_minutes'=> $this->serviceDuration,
            'buffer_minutes'       => 0,
            'number_of_slots'      => $this->shiftSlots,
            'available_days'       => $this->shiftDays,
        ]);

        $this->tenantId = $tenant->id;
        $this->step = 5;
    }

    public function render()
    {
        return view('livewire.tenant.onboarding-wizard', [
            'timezoneOptions' => LocalisationOptions::timezones(),
            'currencyOptions' => collect(LocalisationOptions::currencies())
                ->map(fn ($label, $code) => ['code' => $code, 'label' => $label])
                ->values()
                ->all(),
        ])->layout('layouts.booking', ['tenant' => null]);
    }
}
