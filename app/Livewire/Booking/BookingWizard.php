<?php

namespace App\Livewire\Booking;

use App\Helpers\NotificationHelper;
use App\Services\BookingNotificationService;
use App\Services\BookingPaymentService;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SlotReservation;
use App\Models\Tenant;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class BookingWizard extends Component
{
    use WithFileUploads;
    // Step is NOT in the URL — the URL tracks the actual booking data instead.
    // Step is inferred from the URL params on initial mount (see mount()).
    public int $step = 1;

    // Stored on mount so all Livewire AJAX requests (which bypass the
    // identify.tenant middleware) still know which tenant they belong to.
    public int $tenantId = 0;

    // URL-tracked properties — meaningful, shareable booking state.
    // serviceId is NOT in the URL; it is derived from $servicesParam in mount().
    public ?int $serviceId  = null;

    #[Url(as: 'provider', except: null)]
    public ?int $providerId = null;

    // When a service has 0 or 1 providers the provider step is skipped.
    // We track this so goBack() can jump over it correctly.
    public bool $providerStepSkipped = false;

    #[Url(as: 'date', except: null)]
    public ?string $selectedDate  = null;

    #[Url(as: 'slot', except: null)]
    public ?string $selectedStart = null;

    #[Url(as: 'end', except: null)]
    public ?string $selectedEnd   = null;

    public string $name  = '';
    public string $email = '';
    public string $phone = '';
    public string $note  = '';

    public array $customAnswers = [];
    public array $customFields  = [];

    // Multi-service selection (populated when allowMultipleServices is true).
    // Tracked in the URL as a comma-separated string via $servicesParam.
    public array $serviceIds = [];

    #[Url(as: 'services', except: '')]
    public string $servicesParam = '';

    // Loaded from tenant settings in mount()
    public bool   $allowMultipleServices = false;
    public array  $tenantAvailability    = [];
    public int    $tenantSlotDuration    = 60;   // minutes
    public int    $tenantBufferTime      = 0;    // minutes
    public string $tenantTimezone        = 'UTC';

    // Theme settings
    public string $bookingTheme      = 'classic';
    public string $datePickerStyle   = 'monthly';
    public string $bookingFont       = 'Inter';
    public string $buttonStyle      = 'rounded';
    public bool   $matchSystemTheme = true;
    public bool   $forceDarkMode    = false;

    // Booking behaviour settings
    public array  $blockedDates          = [];
    public int    $minAdvanceNoticeHours = 0;
    public int    $maxBookingsPerDay     = 0;
    public bool   $emailConfirmation     = true;
    public bool   $notifyOwner           = true;

    public ?int    $bookingId    = null;
    public ?string $bookingToken = null;
    public bool    $isDemoBooking = false;

    // ── lifecycle ────────────────────────────────────────────────────

    public function mount(): void
    {
        // mount() runs only on the initial page load, where IdentifyTenant
        // middleware has already set TenantContext. Capture it here.
        $tenant = TenantContext::current();

        $this->tenantId     = $tenant?->id ?? 0;
        $this->customFields = $tenant?->custom_fields ?? [];
        $this->customAnswers = array_fill(0, count($this->customFields), '');

        $this->allowMultipleServices = (bool) \App\Models\Setting::get("tenant_{$this->tenantId}_allow_multiple_services", false);
        $this->tenantTimezone        = $tenant?->timezone ?? 'UTC';

        $this->tenantSlotDuration = (int) \App\Models\Setting::get("tenant_{$this->tenantId}_default_duration", 60);
        $this->tenantBufferTime   = (int) \App\Models\Setting::get("tenant_{$this->tenantId}_buffer_time", 0);

        $rawAvail = \App\Models\Setting::get("tenant_{$this->tenantId}_availability", null);
        $this->tenantAvailability = $rawAvail ? (json_decode($rawAvail, true) ?? []) : [];

        // Booking behaviour settings
        $this->bookingTheme      = \App\Booking\Themes\ThemeRegistry::resolve(\App\Models\Setting::get("tenant_{$this->tenantId}_booking_theme", 'classic'));
        $rawDps = \App\Models\Setting::get("tenant_{$this->tenantId}_date_picker_style", 'monthly');
        $this->datePickerStyle   = in_array($rawDps, ['monthly','weekly']) ? $rawDps : 'monthly';
        $this->bookingFont       = \App\Models\Setting::get("tenant_{$this->tenantId}_booking_font",      'Inter');
        $this->buttonStyle      = \App\Models\Setting::get("tenant_{$this->tenantId}_button_style",      'rounded');
        $rawMatch = \App\Models\Setting::get("tenant_{$this->tenantId}_match_system_theme");
        $this->matchSystemTheme = $rawMatch === null ? true : $rawMatch === '1';
        $rawForce = \App\Models\Setting::get("tenant_{$this->tenantId}_force_dark_mode");
        $this->forceDarkMode    = $rawForce === '1';

        $rawBlocked = \App\Models\Setting::get("tenant_{$this->tenantId}_blocked_dates", null);
        $this->blockedDates          = $rawBlocked ? (json_decode($rawBlocked, true) ?? []) : [];
        $this->minAdvanceNoticeHours = (int) \App\Models\Setting::get("tenant_{$this->tenantId}_min_advance_notice", 0);
        $this->maxBookingsPerDay     = (int) \App\Models\Setting::get("tenant_{$this->tenantId}_max_bookings_per_day", 0);
        $this->emailConfirmation     = (bool) \App\Models\Setting::get("tenant_{$this->tenantId}_email_confirmation", true);
        $this->notifyOwner           = (bool) \App\Models\Setting::get("tenant_{$this->tenantId}_notify_owner", true);

        // Restore serviceIds from the single comma-separated URL param (e.g. services=5 or services=5,11).
        if ($this->servicesParam !== '') {
            $this->serviceIds = array_values(array_filter(array_map('intval', explode(',', $this->servicesParam))));
            $this->serviceId  = $this->serviceIds[0] ?? null;
        }

        // Infer step from URL params so shareable links restore the right step.
        // #[Url] properties are hydrated by Livewire before mount() fires.
        if ($this->selectedStart) {
            $this->step = 4;
        } elseif ($this->selectedDate) {
            $this->step = 3;
        } elseif ($this->serviceId) {
            $providers = $this->getProviders();
            if ($providers->count() === 0) {
                $this->providerStepSkipped = true;
                $this->step = 3;
            } elseif ($providers->count() === 1) {
                $this->providerStepSkipped = true;
                $this->providerId = $providers->first()->id;
                $this->step = 3;
            } else {
                $this->step = $this->providerId ? 3 : 2;
            }
        }
        // else: no URL params → step stays at 1

        // URL pre-fill — developer/agency use: ?service_id=5&name=Alice&email=alice@example.com&phone=555
        // service_id: only when no multi-service param already set; validate tenant ownership.
        if ($this->servicesParam === '') {
            $qServiceId = (int) request()->query('service_id', 0);
            if ($qServiceId > 0 && Service::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->tenantId)
                ->where('id', $qServiceId)
                ->exists()) {
                $this->serviceId = $qServiceId;
            }
        }

        // name/email/phone: set direct properties (fallback for tenants without custom fields)
        // AND populate matching $customAnswers index so the form inputs show the value.
        // Authenticated users get their details pre-filled; URL params take precedence.
        $authUser = auth()->user();
        $prefillMap = [
            'name'  => (string) request()->query('name',  $authUser?->name  ?? ''),
            'email' => (string) request()->query('email', $authUser?->email ?? ''),
            'phone' => (string) request()->query('phone', $authUser?->phone_number ?? ''),
        ];
        foreach ($prefillMap as $prop => $value) {
            if ($value === '') {
                continue;
            }
            match ($prop) {
                'name'  => ($this->name  = $value),
                'email' => ($this->email = $value),
                'phone' => ($this->phone = $value),
            };
            foreach ($this->customFields as $i => $field) {
                $type  = $field['type']  ?? '';
                $label = strtolower($field['label'] ?? '');
                $nameMatch = $prop === 'name' && (
                    in_array($label, ['full name', 'name', 'your name'], true)
                    || str_contains($label, 'full name')
                    || str_contains($label, 'your name')
                    || ($type === 'short_text' && $label === 'name')
                );
                if (($prop === 'email' && $type === 'email')
                    || ($prop === 'phone' && $type === 'phone')
                    || $nameMatch) {
                    $this->customAnswers[$i] = $value;
                    break;
                }
            }
        }
    }

    // ── private helpers ──────────────────────────────────────────────

    private function getTenant(): ?Tenant
    {
        return $this->tenantId ? Tenant::find($this->tenantId) : null;
    }

    // ── computed helpers ────────────────────────────────────────────

    public function getServices(): Collection
    {
        if (! $this->tenantId) {
            return collect();
        }

        return Service::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getSelectedService(): ?Service
    {
        return $this->serviceId
            ? Service::withoutGlobalScope('tenant')->find($this->serviceId)
            : null;
    }

    public function getProviders(): Collection
    {
        if (! $this->serviceId || ! $this->tenantId) {
            return collect();
        }

        return Provider::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantId)
            ->whereHas('services', fn ($q) => $q->where('services.id', $this->serviceId))
            ->with('user')
            ->get();
    }

    public function getAvailableSlots(): Collection
    {
        if (! $this->selectedDate) {
            return collect();
        }

        $date = Carbon::parse($this->selectedDate);

        if ($date->isPast() && ! $date->isToday()) {
            return collect();
        }

        // Blocked dates — admin has closed this day entirely
        if (in_array($date->format('Y-m-d'), $this->blockedDates, true)) {
            return collect();
        }

        // Max bookings per day — tenant-wide cap across all providers
        if ($this->maxBookingsPerDay > 0) {
            $bookedToday = SlotReservation::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->tenantId)
                ->whereDate('date', $date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();
            if ($bookedToday >= $this->maxBookingsPerDay) {
                return collect();
            }
        }

        // ── Specific provider selected ───────────────────────────────
        if ($this->providerId) {
            $provider = Provider::withoutGlobalScope('tenant')
                ->with(['shifts', 'slotOverrides'])
                ->find($this->providerId);

            if (! $provider) {
                return collect();
            }

            return $this->filterByAdvanceNotice($this->slotsForProvider($provider, $date), $date);
        }

        // ── No preference — union of slots across all service providers ─
        $providers = Provider::withoutGlobalScope('tenant')
            ->with(['shifts', 'slotOverrides'])
            ->where('tenant_id', $this->tenantId)
            ->whereHas('services', fn ($q) => $q->where('services.id', $this->serviceId))
            ->get();

        // If no providers are linked to this service, fall back to all tenant providers.
        // This covers newly created services that haven't had providers assigned yet.
        if ($providers->isEmpty()) {
            $providers = Provider::withoutGlobalScope('tenant')
                ->with(['shifts', 'slotOverrides'])
                ->where('tenant_id', $this->tenantId)
                ->get();
        }

        $slots = $providers
            ->flatMap(fn ($p) => $this->slotsForProvider($p, $date)->values())
            ->unique('start')
            ->sortBy('start')
            ->values();

        return $this->filterByAdvanceNotice($slots, $date);
    }

    /** Return available (non-booked) slots for one provider on a date. */
    private function slotsForProvider(Provider $provider, Carbon $date): Collection
    {
        $booked = SlotReservation::withoutGlobalScope('tenant')
            ->where('provider_id', $provider->id)
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('start_time')
            ->map(fn ($t) => substr($t, 0, 5))
            ->toArray();

        // Tenant-level availability takes priority over per-provider shifts
        if (! empty($this->tenantAvailability)) {
            return $this->slotsFromTenantAvailability($date, $booked);
        }

        return $provider->getSlotsForDate($date)
            ->filter(fn ($slot) => $slot['status'] === 'available'
                && ! in_array($slot['start'], $booked, true));
    }

    /** Generate slots from the tenant-level availability settings (FormBuilder → General tab). */
    private function slotsFromTenantAvailability(Carbon $date, array $booked = []): Collection
    {
        if (empty($this->tenantAvailability) || $this->tenantSlotDuration <= 0) {
            return collect();
        }

        $dayKey = match ($date->dayOfWeekIso) {
            1 => 'mon', 2 => 'tue', 3 => 'wed',
            4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun',
        };

        $day = $this->tenantAvailability[$dayKey] ?? null;

        if (! $day || ! ($day['enabled'] ?? false)) {
            return collect();
        }

        $tz        = $this->tenantTimezone ?: 'UTC';
        $dateStr   = $date->format('Y-m-d');
        $slotMins  = $this->tenantSlotDuration;
        $bufferMins = $this->tenantBufferTime;
        $step      = $slotMins + $bufferMins;

        $cursor = Carbon::parse("{$dateStr} {$day['start']}", $tz);
        $close  = Carbon::parse("{$dateStr} {$day['end']}",   $tz);

        $slots = collect();
        while (true) {
            $slotEnd = $cursor->copy()->addMinutes($slotMins);
            if ($slotEnd->greaterThan($close)) {
                break;
            }
            $startStr = $cursor->format('H:i');
            $endStr   = $slotEnd->format('H:i');

            if (! in_array($startStr, $booked, true)) {
                $slots->push(['start' => $startStr, 'end' => $endStr, 'status' => 'available']);
            }

            $cursor->addMinutes($step);
        }

        return $slots;
    }

    /** Remove slots that start sooner than the configured minimum advance notice. */
    private function filterByAdvanceNotice(Collection $slots, Carbon $date): Collection
    {
        if ($this->minAdvanceNoticeHours <= 0) {
            return $slots;
        }

        $tz       = $this->tenantTimezone ?: 'UTC';
        $earliest = now($tz)->addHours($this->minAdvanceNoticeHours);
        $dateStr  = $date->format('Y-m-d');

        return $slots->filter(function ($slot) use ($dateStr, $tz, $earliest) {
            $slotStart = Carbon::parse("{$dateStr} {$slot['start']}", $tz);
            return $slotStart->greaterThan($earliest);
        })->values();
    }

    // Called from Alpine signature pad via $wire.setCustomAnswer(idx, dataUrl)
    public function setCustomAnswer(int $idx, string $value): void
    {
        $this->customAnswers[$idx] = $value;
    }

    // ── step navigation ─────────────────────────────────────────────

    public function selectService(int $id): void
    {
        if ($this->allowMultipleServices) {
            // Toggle in/out of the selection array
            if (in_array($id, $this->serviceIds, true)) {
                $this->serviceIds = array_values(array_filter($this->serviceIds, fn ($s) => $s !== $id));
            } else {
                $this->serviceIds[] = $id;
            }
            // Primary serviceId = first selected
            $this->serviceId     = $this->serviceIds[0] ?? null;
            $this->servicesParam = implode(',', $this->serviceIds);
        } else {
            $this->serviceId     = $id;
            $this->serviceIds    = [$id];
            $this->servicesParam = (string) $id;
        }
        $this->providerId    = null;
        $this->selectedDate  = null;
        $this->selectedStart = null;
        $this->selectedEnd   = null;
        // Do NOT advance step — user must click Continue.
    }

    public function continueFromService(): void
    {
        if (! $this->serviceId) {
            return;
        }

        $providers = $this->getProviders();

        if ($providers->count() === 0) {
            $this->providerStepSkipped = true;
            $this->step = 3;
        } else {
            $this->providerStepSkipped = false;
            $this->step = 2;
        }
    }

    // Selecting a provider just highlights the card — Continue button advances.
    // Pass 0 to mean "No preference".
    public function selectProvider(int $id): void
    {
        $this->providerId    = $id ?: null; // 0 = no preference → null
        $this->selectedDate  = null;
        $this->selectedStart = null;
        $this->selectedEnd   = null;
        // Do NOT advance step here — user must click Continue.
    }

    public function continueFromProvider(): void
    {
        // providerId === null means "No preference" — that's valid.
        $this->step = 3;
    }

    public function selectSlot(string $start, string $end): void
    {
        $this->selectedStart = $start;
        $this->selectedEnd   = $end;
    }

    public function continueFromSlot(): void
    {
        if (! $this->selectedStart) {
            return;
        }
        $this->step = 4;
    }

    public function goBack(): void
    {
        if ($this->step <= 1) {
            return;
        }

        // If provider step was skipped, jump from step 3 all the way back to 1.
        if ($this->step === 3 && $this->providerStepSkipped) {
            $this->step = 1;
            $this->providerStepSkipped = false;
            $this->providerId = null;
            return;
        }

        $this->step--;
    }

    // ── form submission ─────────────────────────────────────────────

    /** Map customAnswers back to the named booking fields (name/email/phone/note). */
    /** Build validation rules + human-readable attribute names from customFields. */
    private function buildFieldRules(): array
    {
        $rules      = [];
        $attributes = [];

        foreach ($this->customFields as $i => $field) {
            $key        = "customAnswers.$i";
            $label      = $field['label'] ?? ('Field ' . ($i + 1));
            $required   = $field['required'] ?? false;

            $rules[$key] = match ($field['type'] ?? 'short_text') {
                'email'       => $required ? 'required|email|max:255'                         : 'nullable|email|max:255',
                'signature'   => $required ? 'required|string|max:200000'                    : 'nullable|string|max:200000',
                'file_upload' => $required ? 'required|file|max:10240'                       : 'nullable|file|max:10240',
                default       => $required ? 'required|string|max:500'                       : 'nullable|string|max:500',
            };
            $attributes[$key] = $label;
        }

        return [$rules, $attributes];
    }

    private function syncNamedFieldsFromAnswers(): void
    {
        foreach ($this->customFields as $i => $field) {
            $answer = $this->customAnswers[$i] ?? '';
            match (true) {
                $field['type'] === 'email' => ($this->email = $answer) && false,
                $field['type'] === 'phone' => ($this->phone = $answer) && false,
                in_array(strtolower($field['label'] ?? ''), ['full name', 'name', 'your name']) => ($this->name = $answer) && false,
                in_array(strtolower($field['label'] ?? ''), ['notes', 'note', 'message', 'special requests']) => ($this->note = $answer) && false,
                default => null,
            };
        }
    }

    /**
     * Validate the details form and advance to the confirmation review step (5).
     * Actual booking creation happens in confirm() when the user clicks "Confirm booking".
     */
    public function continueToConfirm(): void
    {
        $this->syncNamedFieldsFromAnswers();

        [$rules, $attributes] = $this->buildFieldRules();

        $rules['name']  = 'required|string|max:255';
        $rules['email'] = 'required|email|max:255';
        $attributes['name']  = 'Full name';
        $attributes['email'] = 'Email';

        $this->validate($rules, [], $attributes);
        $this->step = 5;
    }

    /**
     * Create the booking (called from the review step).
     * Advances to step 6 (success screen).
     */
    public function confirm(): void
    {
        $rateLimitKey = 'booking_submit_' . $this->tenantId . '_' . request()->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $this->addError('selectedDate', __('Too many booking attempts. Please wait a minute and try again.'));
            return;
        }
        RateLimiter::hit($rateLimitKey, 60);

        $this->syncNamedFieldsFromAnswers();

        $tenant = $this->getTenant();
        $fields = $this->customFields;

        [$rules, $attributes] = $this->buildFieldRules();

        $rules['name']  = 'required|string|max:255';
        $rules['email'] = 'required|email|max:255';
        $attributes['name']  = 'Full name';
        $attributes['email'] = 'Email';

        $this->validate($rules, [], $attributes);

        // Enforce plan limits
        $plan = $tenant?->plan;

        if ($plan && $plan->max_bookings_per_month !== null) {
            $usedThisMonth = SlotReservation::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->tenantId)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->whereNotIn('status', ['cancelled'])
                ->count();

            if ($usedThisMonth >= $plan->max_bookings_per_month) {
                $this->addError('email', __('This business has reached its monthly booking limit. Please contact them directly.'));
                return;
            }
        }

        $service = $this->getSelectedService();

        // Persist any uploaded files before the transaction — Livewire temp files
        // cannot be moved inside a DB transaction (filesystem op, not DB).
        $answers = $this->customAnswers;
        foreach ($fields as $i => $field) {
            if (($field['type'] ?? '') === 'file_upload' && isset($answers[$i]) && $answers[$i] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $answers[$i] = $answers[$i]->store('booking-uploads', 'public');
            }
        }

        // Build custom_answers payload — include all selected service IDs when multi-select is used
        $answersPayload = empty($fields) ? [] : array_values($answers);
        if ($this->allowMultipleServices && count($this->serviceIds) > 1) {
            $answersPayload = array_merge($answersPayload, ['_service_ids' => $this->serviceIds]);
        }

        // Wrap provider resolution + booking creation in a transaction with a
        // row-level lock so two concurrent requests for the same slot cannot both
        // pass the availability check. UniqueConstraintViolationException is the
        // safety net if the lock races with a request from a different process.
        try {
            $booking = DB::transaction(function () use ($tenant, $service, $answersPayload) {
                // Resolve provider inside the transaction so the availability check
                // and the INSERT are atomic. lockForUpdate prevents other
                // transactions from inserting a conflicting booking while we decide.
                $assignedProviderId = $this->providerId;
                if ($assignedProviderId === null) {
                    $assignedProviderId = Provider::withoutGlobalScope('tenant')
                        ->where('tenant_id', $this->tenantId)
                        ->when($this->serviceId, fn ($q) =>
                            $q->whereHas('services', fn ($s) => $s->where('services.id', $this->serviceId))
                        )
                        ->whereDoesntHave('bookings', fn ($q) => $q
                            ->whereDate('date', $this->selectedDate)
                            ->where('start_time', $this->selectedStart)
                            ->whereIn('status', ['pending', 'confirmed'])
                        )
                        ->lockForUpdate()
                        ->value('id');

                    if ($assignedProviderId === null) {
                        $assignedProviderId = Provider::withoutGlobalScope('tenant')
                            ->where('tenant_id', $this->tenantId)
                            ->whereDoesntHave('bookings', fn ($q) => $q
                                ->whereDate('date', $this->selectedDate)
                                ->where('start_time', $this->selectedStart)
                                ->whereIn('status', ['pending', 'confirmed'])
                            )
                            ->lockForUpdate()
                            ->value('id');
                    }
                }

                if ($assignedProviderId === null) {
                    return null;
                }

                return SlotReservation::withoutGlobalScope('tenant')->create([
                    'tenant_id'          => $this->tenantId,
                    'service_id'         => $this->serviceId,
                    'provider_id'        => $assignedProviderId,
                    'date'               => $this->selectedDate,
                    'start_time'         => $this->selectedStart,
                    'end_time'           => $this->selectedEnd ?? $this->selectedStart,
                    'name'               => $this->name,
                    'email'              => $this->email,
                    'phone'              => $this->phone,
                    'note'               => $this->note,
                    'custom_answers'     => empty($answersPayload) ? null : $answersPayload,
                    'cancellation_token' => Str::random(32),
                    'status'             => 'confirmed',
                    'amount'             => $service?->price ?? 0,
                    'currency'           => $service?->currency ?? ($tenant?->currency ?? 'INR'),
                    'payment_status'     => 'pending',
                    'is_verified'        => true,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            // Two concurrent requests raced to the same slot; the second one lost.
            $this->selectedStart = null;
            $this->selectedEnd   = null;
            $this->step = 3;
            $this->addError('selectedDate', __('Sorry, that slot was just taken. Please choose another time.'));
            return;
        }

        // No provider available — slot was taken between selection and submission
        if ($booking === null) {
            $this->selectedStart = null;
            $this->selectedEnd   = null;
            $this->step = 3;
            $this->addError('selectedDate', __('Sorry, that slot was just taken. Please choose another time.'));
            return;
        }

        $this->isDemoBooking = ! $booking->exists;

        if ($booking->exists) {
            $payments = app(BookingPaymentService::class);

            if ($payments->requiresPaymentAtBooking($booking)) {
                $token = $booking->cancellation_token;
                $checkoutUrl = $payments->createCheckoutUrl(
                    $booking,
                    route('booking.payment.success', ['token' => $token]),
                    route('booking.payment.cancel', ['token' => $token]),
                );

                if ($checkoutUrl) {
                    $this->redirect($checkoutUrl);

                    return;
                }
            }

            app(BookingNotificationService::class)->dispatchForNewBooking(
                $booking,
                $this->emailConfirmation,
                $this->notifyOwner,
            );
            $payments->maybeSyncCalendar($booking);
        }

        // In DEMO_MODE the Eloquent saving event is blocked so $booking->id is null.
        // Use 9999999 as a sentinel so the success screen still renders with a demo notice.
        $this->bookingId    = $booking->id ?? 9999999;
        $this->bookingToken = $booking->cancellation_token ?? null;
        $this->step = 6;
    }

    private function dispatchBookingWebNotification(string $event, SlotReservation $booking, string $heading, string $message): void
    {
        try {
            $url = rescue(fn () => route('filament.tenant.resources.bookings.view', ['record' => $booking->id]), null);
            NotificationHelper::sendToTenantWebUsers($event, $booking->tenant_id, $heading, $message, $url);
        } catch (\Throwable) {
            // Non-critical — never block the booking flow
        }
    }

    // ── render ───────────────────────────────────────────────────────

    public function render()
    {
        $tenant = $this->getTenant();

        return view('livewire.booking.booking-wizard', [
            'tenant'                  => $tenant,
            'services'                => $this->getServices(),
            'selectedService'         => $this->getSelectedService(),
            'providers'               => $this->getProviders(),
            'availableSlots'          => $this->getAvailableSlots(),
            'customFields'            => $this->customFields,
            'allowMultipleServices'   => $this->allowMultipleServices,
            'activeServiceIds'        => $this->serviceIds,
            'successTitle'            => Setting::get("tenant_{$this->tenantId}_success_title", __("You're booked!")),
            'successBody'             => Setting::get("tenant_{$this->tenantId}_success_body",  __("We just sent a confirmation to your email. See you soon!")),
            'tenantPhone'             => $tenant?->phone ?? '',
            'tenantAddress'           => $tenant?->address ?? '',
            'tenantWebsite'           => $tenant?->website_url ?? '',
            'tenantTagline'           => $tenant?->booking_page_tagline ?? '',
            'showCancellationPolicy'  => (bool) Setting::get("tenant_{$this->tenantId}_show_cancellation_policy", false),
            'cancellationPolicyText'  => (string) Setting::get("tenant_{$this->tenantId}_cancellation_policy", ''),
            'tenantAvailability'      => $this->tenantAvailability,
            'isDemoBooking'           => $this->isDemoBooking,
            'bookingFont'             => $this->bookingFont,
            'buttonStyle'             => $this->buttonStyle,
        ])->layout('layouts.booking', [
            'tenant'          => $tenant,
            'bookingFont'     => $this->bookingFont,
            'matchSystemTheme'=> $this->matchSystemTheme,
            'forceDarkMode'   => $this->forceDarkMode,
        ]);
    }
}
