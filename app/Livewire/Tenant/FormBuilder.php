<?php

namespace App\Livewire\Tenant;

use App\Models\Provider;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class FormBuilder extends Component
{
    use WithFileUploads;
    // ── core ─────────────────────────────────────────────────────────
    public int     $tenantId   = 0;
    public string  $formTitle  = 'Booking Form';
    public array   $fields     = [];
    public ?string $selectedId = null;
    public bool    $isDirty    = false;
    public array   $history    = [];
    public array   $future     = [];

    #[Url(as: 'tab', except: 'settings')]
    public string  $activeTab  = 'settings';

    // ── theme ─────────────────────────────────────────────────────────
    public string $bookingTheme       = 'classic';
    public string $datePickerStyle    = 'monthly';
    public string $bookingColor       = '#7c3aed';
    public string $bookingFont       = 'Inter';
    public string $buttonStyle       = 'rounded';
    public bool   $matchSystemTheme  = true;
    public bool   $forceDarkMode     = false;

    // ── success page ─────────────────────────────────────────────────
    public string $successTitle = "You're booked!";
    public string $successBody  = "We just sent a confirmation to your email. See you soon!";

    // ── settings ─────────────────────────────────────────────────────
    public string $tenantName     = '';
    public string $tenantSlugEdit = '';
    public string $tenantEmail    = '';
    public string $tenantPhone    = '';
    public string $tenantAddress  = '';
    public string $tenantTagline  = '';
    public string $tenantWebsite  = '';
    public string $tenantLogoPath = '';        // current stored logo path
    public $logoFile              = null;      // pending upload (WithFileUploads)
    public string $tenantTimezone         = 'UTC';
    public string $tenantCurrency         = 'USD';
    public string $settingsError          = '';
    public string $svcError               = '';
    public string $prvError               = '';
    public bool   $allowMultipleServices    = false;
    public bool   $allowClientCancellation = true;
    public bool   $showCancellationPolicy  = false;
    public string $cancellationPolicy      = '';

    // ── general timing ────────────────────────────────────────────
    public int    $defaultDuration = 60;
    public int    $bufferTime      = 15;

    // ── availability ──────────────────────────────────────────────
    public array  $availability = [
        'mon' => ['enabled' => true,  'start' => '09:00', 'end' => '18:00'],
        'tue' => ['enabled' => true,  'start' => '09:00', 'end' => '18:00'],
        'wed' => ['enabled' => true,  'start' => '09:00', 'end' => '18:00'],
        'thu' => ['enabled' => true,  'start' => '09:00', 'end' => '18:00'],
        'fri' => ['enabled' => true,  'start' => '09:00', 'end' => '18:00'],
        'sat' => ['enabled' => false, 'start' => '10:00', 'end' => '16:00'],
        'sun' => ['enabled' => false, 'start' => '00:00', 'end' => '00:00'],
    ];
    public array  $blockedDates   = [];
    public string $newBlockedDate = '';

    // ── limits ────────────────────────────────────────────────────
    public int    $maxBookingsPerDay  = 0;
    public string $minAdvanceNotice   = '0';

    // ── notifications ─────────────────────────────────────────────
    public bool   $emailConfirmation = true;
    public bool   $smsReminder       = false;
    public bool   $notifyOwner       = true;
    public string $webhookUrl        = '';
    public string $webhookSecret     = '';

    // ── delete confirm modals ─────────────────────────────────────────
    public bool    $showDeleteFieldConfirm = false;
    public ?string $deleteFieldId         = null;
    public string  $deleteFieldLabel      = '';

    public bool    $showDeleteConfirm      = false;
    public ?int    $deleteServiceId        = null;
    public string  $deleteServiceName      = '';

    public bool    $showDeleteProviderConfirm = false;
    public ?int    $deleteProviderId          = null;
    public string  $deleteProviderName        = '';

    // ── service panel ─────────────────────────────────────────────────
    public bool   $showServiceModal  = false;
    public ?int   $editingServiceId  = null;
    public string $svcName           = '';
    public string $svcDescription    = '';
    public string $svcCategory       = '';
    public int    $svcDuration       = 30;
    public float  $svcPrice          = 0;
    public string $svcColor          = '#6d28d9';
    public bool   $svcActive         = true;
    public int    $svcSortOrder      = 0;
    public array  $svcProviderIds    = [];

    // ── provider panel ────────────────────────────────────────────────
    public bool    $showProviderModal  = false;
    public ?int    $editingProviderId  = null;
    public ?int    $prvUserId          = null;
    public string  $prvName            = '';
    public string  $prvEmail           = '';
    public string  $prvJobTitle        = '';
    public string  $prvExperience      = '';
    public string  $prvBio             = '';
    public string  $prvColor           = '#7c3aed';
    public bool    $prvActive          = true;
    public array   $prvServiceIds      = [];

    // ── lifecycle ────────────────────────────────────────────────────

    public function mount(): void
    {
        $tenant         = TenantContext::current();
        $this->tenantId = $tenant?->id ?? 0;

        $this->formTitle = ($tenant?->name ?? __('Booking')) . ' ' . __('Booking Form');
        $this->fields    = $this->normalize($tenant?->custom_fields ?? []);

        // Seed defaults when no fields have been configured yet
        if (empty($this->fields)) {
            $this->fields = [
                ['id' => 'f_name',  'type' => 'short_text', 'label' => __('Full name'),  'placeholder' => __('Jane Doe'),             'required' => true,  'hidden' => false, 'options' => [], 'condition_field' => null, 'condition_value' => ''],
                ['id' => 'f_email', 'type' => 'email',      'label' => __('Email'),      'placeholder' => 'jane@example.com',         'required' => true,  'hidden' => false, 'options' => [], 'condition_field' => null, 'condition_value' => ''],
                ['id' => 'f_phone', 'type' => 'phone',      'label' => __('Phone'),      'placeholder' => '+1 (555) 123-4567',        'required' => false, 'hidden' => false, 'options' => [], 'condition_field' => null, 'condition_value' => ''],
                ['id' => 'f_notes', 'type' => 'short_text', 'label' => __('Notes'),      'placeholder' => __('Any special requests…'), 'required' => false, 'hidden' => false, 'options' => [], 'condition_field' => null, 'condition_value' => ''],
            ];
            $this->isDirty = true;
        }

        $this->bookingTheme      = \App\Booking\Themes\ThemeRegistry::resolve(Setting::get("tenant_{$this->tenantId}_booking_theme", 'classic'));
        $rawDps = Setting::get("tenant_{$this->tenantId}_date_picker_style", 'monthly');
        $this->datePickerStyle   = in_array($rawDps, ['monthly', 'weekly']) ? $rawDps : 'monthly';
        $this->bookingColor      = $tenant?->booking_page_color ?? '#7c3aed';
        $this->bookingFont      = Setting::get("tenant_{$this->tenantId}_booking_font",      'Inter');
        $this->buttonStyle      = Setting::get("tenant_{$this->tenantId}_button_style",      'rounded');
        $rawMatch = Setting::get("tenant_{$this->tenantId}_match_system_theme");
        $this->matchSystemTheme = $rawMatch === null ? true : $rawMatch === '1';
        $rawForce = Setting::get("tenant_{$this->tenantId}_force_dark_mode");
        $this->forceDarkMode    = $rawForce === '1';

        $this->successTitle = Setting::get("tenant_{$this->tenantId}_success_title", __("You're booked!"));
        $this->successBody  = Setting::get("tenant_{$this->tenantId}_success_body",  __("We just sent a confirmation to your email. See you soon!"));

        $this->tenantName     = $tenant?->name ?? '';
        $this->tenantSlugEdit = $tenant?->slug ?? '';
        $this->tenantEmail    = $tenant?->email ?? '';
        $this->tenantPhone    = $tenant?->phone ?? '';
        $this->tenantAddress  = $tenant?->address ?? '';
        $this->tenantTagline  = $tenant?->booking_page_tagline ?? '';
        $this->tenantWebsite  = $tenant?->website_url ?? '';
        $this->tenantLogoPath = $tenant?->logo ?? '';
        $this->tenantTimezone = $tenant?->timezone ?? 'UTC';
        $this->tenantCurrency = $tenant?->currency ?? 'INR';

        $this->allowMultipleServices    = (bool) Setting::get("tenant_{$this->tenantId}_allow_multiple_services", false);
        $this->allowClientCancellation = (bool) Setting::get("tenant_{$this->tenantId}_allow_client_cancellation", true);
        $this->showCancellationPolicy  = (bool) Setting::get("tenant_{$this->tenantId}_show_cancellation_policy", false);
        $this->cancellationPolicy      = (string) Setting::get("tenant_{$this->tenantId}_cancellation_policy", '');

        $this->defaultDuration = (int) Setting::get("tenant_{$this->tenantId}_default_duration", 60);
        $this->bufferTime      = (int) Setting::get("tenant_{$this->tenantId}_buffer_time", 15);

        $avail = Setting::get("tenant_{$this->tenantId}_availability", null);
        if ($avail) {
            $decoded = json_decode($avail, true);
            if (is_array($decoded)) {
                $this->availability = array_merge($this->availability, $decoded);
            }
        }

        $blocked = Setting::get("tenant_{$this->tenantId}_blocked_dates", null);
        if ($blocked) {
            $decoded = json_decode($blocked, true);
            if (is_array($decoded)) {
                $this->blockedDates = $decoded;
            }
        }

        $this->maxBookingsPerDay = (int) Setting::get("tenant_{$this->tenantId}_max_bookings_per_day", 0);
        $this->minAdvanceNotice  = (string) Setting::get("tenant_{$this->tenantId}_min_advance_notice", '0');
        $this->emailConfirmation = (bool) Setting::get("tenant_{$this->tenantId}_email_confirmation", true);
        $this->smsReminder       = (bool) Setting::get("tenant_{$this->tenantId}_sms_reminder", false);
        $this->notifyOwner       = (bool) Setting::get("tenant_{$this->tenantId}_notify_owner", true);
        $this->webhookUrl        = (string) Setting::get("tenant_{$this->tenantId}_webhook_url", '');
        $this->webhookSecret     = (string) Setting::get("tenant_{$this->tenantId}_webhook_secret", '');
    }

    // ── tabs ─────────────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        $this->activeTab  = $tab;
        $this->selectedId = null;
        $this->settingsError = '';
    }

    // ── helpers ──────────────────────────────────────────────────────

    private function normalize(array $raw): array
    {
        $fieldItems = array_filter($raw, fn ($f) => is_array($f) && isset($f['type']));

        return array_values(array_map(function (array $f): array {
            return [
                'id'              => $f['id'] ?? ('f' . Str::random(6)),
                'type'            => $this->mapLegacy($f['type'] ?? 'short_text'),
                'label'           => $f['label'] ?? '',
                'placeholder'     => $f['placeholder'] ?? '',
                'required'        => (bool) ($f['required'] ?? false),
                'hidden'          => (bool) ($f['hidden'] ?? false),
                'options'         => array_values(array_filter($f['options'] ?? [], 'is_string')),
                'condition_field' => $f['condition_field'] ?? null,
                'condition_value' => $f['condition_value'] ?? '',
            ];
        }, $fieldItems));
    }

    private function mapLegacy(string $type): string
    {
        return match ($type) {
            'text'     => 'short_text',
            'textarea' => 'short_text',
            'select'   => 'dropdown',
            default    => $type,
        };
    }

    private function defaults(string $type): array
    {
        return match ($type) {
            'short_text'  => ['label' => __('Short text'),    'placeholder' => __('Type your answer')],
            'email'       => ['label' => __('Email address'), 'placeholder' => 'you@email.com'],
            'phone'       => ['label' => __('Phone number'),  'placeholder' => '+1 (555) 123-4567'],
            'dropdown'    => ['label' => __('Dropdown'),      'placeholder' => __('Pick one'),   'options' => [__('Option 1'), __('Option 2')]],
            'date_picker' => ['label' => __('Date'),          'placeholder' => ''],
            'time_slot'   => ['label' => __('Time'),          'placeholder' => ''],
            'file_upload' => ['label' => __('Upload file'),   'placeholder' => ''],
            'checkbox'    => ['label' => __('Checkbox'),      'placeholder' => ''],
            'radio_group' => ['label' => __('Radio group'),   'placeholder' => '', 'options' => [__('Option 1'), __('Option 2')]],
            'signature'   => ['label' => __('Signature'),     'placeholder' => ''],
            default       => ['label' => ucfirst(str_replace('_', ' ', $type)), 'placeholder' => ''],
        };
    }

    private function pushHistory(): void
    {
        $this->history[] = $this->fields;
        if (count($this->history) > 30) {
            array_shift($this->history);
        }
        $this->future = [];
    }

    // ── undo / redo ──────────────────────────────────────────────────

    public function undo(): void
    {
        if (empty($this->history)) {
            return;
        }
        $this->future[]   = $this->fields;
        $this->fields     = array_pop($this->history);
        $this->isDirty    = true;
        $this->selectedId = null;
    }

    public function redo(): void
    {
        if (empty($this->future)) {
            return;
        }
        $this->history[] = $this->fields;
        $this->fields    = array_pop($this->future);
        $this->isDirty   = true;
        $this->selectedId = null;
    }

    private function idx(string $id): ?int
    {
        foreach ($this->fields as $i => $f) {
            if ($f['id'] === $id) {
                return $i;
            }
        }
        return null;
    }

    // ── field management ─────────────────────────────────────────────

    public function addField(string $type): void
    {
        $this->pushHistory();
        $id = 'f' . Str::random(6);
        $this->fields[] = array_merge([
            'id'              => $id,
            'type'            => $type,
            'required'        => false,
            'hidden'          => false,
            'options'         => [],
            'condition_field' => null,
            'condition_value' => '',
        ], $this->defaults($type));

        $this->selectedId = $id;
        $this->isDirty    = true;
    }

    public function confirmDeleteField(string $id): void
    {
        if (count($this->fields) <= 1) {
            $this->dispatch('toast-error', message: __('At least one field is required.'));
            return;
        }
        $field = collect($this->fields)->firstWhere('id', $id);
        $this->deleteFieldId          = $id;
        $this->deleteFieldLabel       = $field['label'] ?? __('this field');
        $this->showDeleteFieldConfirm = true;
    }

    public function cancelDeleteField(): void
    {
        $this->showDeleteFieldConfirm = false;
        $this->deleteFieldId          = null;
        $this->deleteFieldLabel       = '';
    }

    public function deleteField(string $id): void
    {
        if (count($this->fields) <= 1) {
            $this->dispatch('toast-error', message: __('At least one field is required.'));
            return;
        }
        $this->pushHistory();
        $this->fields = array_values(
            array_filter($this->fields, fn ($f) => $f['id'] !== $id)
        );
        if ($this->selectedId === $id) {
            $this->selectedId = null;
        }
        $this->isDirty                = true;
        $this->showDeleteFieldConfirm = false;
        $this->deleteFieldId          = null;
        $this->deleteFieldLabel       = '';
    }

    public function selectField(string $id): void
    {
        $this->selectedId = $id; // always select — no toggle
    }

    public function reorder(array $ids): void
    {
        $this->pushHistory();
        $map      = collect($this->fields)->keyBy('id');
        $reordered = array_values(array_filter(
            array_map(fn ($id) => $map->get($id), $ids)
        ));

        if (count($reordered) === count($this->fields)) {
            $this->fields  = $reordered;
            $this->isDirty = true;
        }
    }

    // ── property editing ─────────────────────────────────────────────

    public function updateProp(string $id, string $key, mixed $value): void
    {
        static $allowed = ['label', 'placeholder', 'required', 'hidden', 'options', 'condition_field', 'condition_value'];
        if (! in_array($key, $allowed, true)) {
            return;
        }
        $i = $this->idx($id);
        if ($i !== null) {
            $this->pushHistory();
            $this->fields[$i][$key] = $value;
            $this->isDirty = true;
        }
    }

    public function duplicateField(string $id): void
    {
        $i = $this->idx($id);
        if ($i === null) {
            return;
        }
        $this->pushHistory();
        $copy       = $this->fields[$i];
        $copy['id'] = 'f' . Str::random(6);
        array_splice($this->fields, $i + 1, 0, [$copy]);
        $this->selectedId = $copy['id'];
        $this->isDirty    = true;
    }

    public function addOption(string $id): void
    {
        $i = $this->idx($id);
        if ($i !== null) {
            $this->fields[$i]['options'][] = __('Option') . ' ' . (count($this->fields[$i]['options']) + 1);
            $this->isDirty = true;
        }
    }

    public function updateOption(string $id, int $index, string $value): void
    {
        $i = $this->idx($id);
        if ($i !== null) {
            $this->fields[$i]['options'][$index] = $value;
            $this->isDirty = true;
        }
    }

    public function removeOption(string $id, int $index): void
    {
        $i = $this->idx($id);
        if ($i !== null) {
            array_splice($this->fields[$i]['options'], $index, 1);
            $this->isDirty = true;
        }
    }

    // ── builder persistence ──────────────────────────────────────────

    public function save(): void
    {
        if (! $this->tenantId) {
            return;
        }

        \DB::table('tenants')->where('id', $this->tenantId)->update([
            'custom_fields' => json_encode(array_values($this->fields)),
            'updated_at'    => now(),
        ]);

        $this->isDirty = false;
        $this->dispatch('form-builder-saved');
    }

    public function discard(): void
    {
        $tenant           = Tenant::find($this->tenantId);
        $this->fields     = $this->normalize($tenant?->custom_fields ?? []);
        $this->selectedId = null;
        $this->isDirty    = false;
    }

    // ── logo ──────────────────────────────────────────────────────────

    public function removeLogo(): void
    {
        if ($this->tenantLogoPath) {
            Storage::disk('public')->delete($this->tenantLogoPath);
        }
        \DB::table('tenants')->where('id', $this->tenantId)->update(['logo' => null, 'updated_at' => now()]);
        $this->tenantLogoPath = '';
        $this->logoFile       = null;
        $this->dispatch('form-builder-saved');
    }

    // ── blocked dates ─────────────────────────────────────────────────

    public function addBlockedDate(): void
    {
        $date = trim($this->newBlockedDate);
        $this->newBlockedDate = '';
        if (! $date) {
            return;
        }
        if (! strtotime($date)) {
            $this->dispatch('toast-error', message: __('Invalid date format. Use YYYY-MM-DD.'));
            return;
        }
        if (! in_array($date, $this->blockedDates, true)) {
            $this->blockedDates[] = $date;
            sort($this->blockedDates);
        }
    }

    public function removeBlockedDate(string $date): void
    {
        $this->blockedDates = array_values(
            array_filter($this->blockedDates, fn ($d) => $d !== $date)
        );
    }

    // ── theme ─────────────────────────────────────────────────────────

    public function saveTheme(): void
    {
        if (! $this->tenantId) {
            return;
        }

        \DB::table('tenants')->where('id', $this->tenantId)->update([
            'booking_page_color' => $this->bookingColor,
            'updated_at'         => now(),
        ]);

        Setting::set("tenant_{$this->tenantId}_booking_theme",       $this->bookingTheme);
        Setting::set("tenant_{$this->tenantId}_date_picker_style",  $this->datePickerStyle);
        Setting::set("tenant_{$this->tenantId}_booking_font",       $this->bookingFont);
        Setting::set("tenant_{$this->tenantId}_button_style",      $this->buttonStyle);
        Setting::set("tenant_{$this->tenantId}_match_system_theme", $this->matchSystemTheme ? '1' : '0');
        Setting::set("tenant_{$this->tenantId}_force_dark_mode",    $this->forceDarkMode    ? '1' : '0');

        TenantContext::set(Tenant::find($this->tenantId));
        $this->dispatch('form-builder-saved');
    }

    // ── success page ─────────────────────────────────────────────────

    public function saveSuccessPage(): void
    {
        // Use DB::table() to bypass the eloquent.saving listener in DEMO_MODE
        \DB::table('settings')->updateOrInsert(
            ['key' => "tenant_{$this->tenantId}_success_title"],
            ['value' => $this->successTitle, 'group' => 'tenant']
        );
        \DB::table('settings')->updateOrInsert(
            ['key' => "tenant_{$this->tenantId}_success_body"],
            ['value' => $this->successBody, 'group' => 'tenant']
        );
        // Invalidate the cache that Setting::get() populates — same as Setting::set() does
        \Illuminate\Support\Facades\Cache::forget("setting.tenant_{$this->tenantId}_success_title");
        \Illuminate\Support\Facades\Cache::forget("setting.tenant_{$this->tenantId}_success_body");
        $this->dispatch('form-builder-saved');
    }

    // ── settings ─────────────────────────────────────────────────────

    public function saveSettings(): void
    {
        $this->settingsError = '';

        $tenant = Tenant::find($this->tenantId);
        if (! $tenant) {
            return;
        }

        $slug = trim($this->tenantSlugEdit);
        if ($slug && $slug !== $tenant->slug) {
            if (Tenant::where('slug', $slug)->where('id', '!=', $this->tenantId)->exists()) {
                $this->settingsError = __('This URL slug is already taken.');
                return;
            }
        }

        $webhookUrl = trim($this->webhookUrl);
        if ($webhookUrl && ! filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            $this->settingsError = __('Webhook URL is not a valid URL.');
            return;
        }

        // Validate & store logo if a new file was uploaded
        $logoPath = $tenant->logo;
        if ($this->logoFile) {
            $this->validate(['logoFile' => 'image|max:2048']);
            // Delete old logo
            if ($logoPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $this->logoFile->store('logos', 'public');
            $this->tenantLogoPath = $logoPath;
            $this->logoFile       = null;
        }

        \DB::table('tenants')->where('id', $this->tenantId)->update([
            'name'                 => trim($this->tenantName),
            'slug'                 => $slug ?: $tenant->slug,
            'email'                => trim($this->tenantEmail),
            'phone'                => trim($this->tenantPhone) ?: null,
            'address'              => trim($this->tenantAddress) ?: null,
            'website_url'          => trim($this->tenantWebsite) ?: null,
            'booking_page_tagline' => trim($this->tenantTagline) ?: null,
            'logo'                 => $logoPath ?: null,
            'timezone'             => $this->tenantTimezone,
            'currency'             => $this->tenantCurrency,
            'updated_at'           => now(),
        ]);

        // Propagate currency change to all services so price_formatted stays consistent
        \DB::table('services')
            ->where('tenant_id', $this->tenantId)
            ->update(['currency' => $this->tenantCurrency]);

        $this->tenantSlugEdit = $slug ?: $tenant->slug;
        $this->formTitle      = ($this->tenantName ?: 'Booking') . ' Booking Form';

        // Refresh TenantContext so subsequent renders (including booking page) see updated values
        TenantContext::set(Tenant::find($this->tenantId));

        // Save booking-behavior settings via DB::table (bypasses DEMO_MODE event block)
        $behaviorSettings = [
            "tenant_{$this->tenantId}_allow_multiple_services"    => $this->allowMultipleServices    ? '1' : '0',
            "tenant_{$this->tenantId}_allow_client_cancellation" => $this->allowClientCancellation  ? '1' : '0',
            "tenant_{$this->tenantId}_show_cancellation_policy"  => $this->showCancellationPolicy   ? '1' : '0',
            "tenant_{$this->tenantId}_cancellation_policy"       => $this->cancellationPolicy,
            "tenant_{$this->tenantId}_default_duration"         => (string) $this->defaultDuration,
            "tenant_{$this->tenantId}_buffer_time"              => (string) $this->bufferTime,
            "tenant_{$this->tenantId}_availability"             => json_encode($this->availability),
            "tenant_{$this->tenantId}_blocked_dates"            => json_encode(array_values($this->blockedDates)),
            "tenant_{$this->tenantId}_max_bookings_per_day"     => (string) $this->maxBookingsPerDay,
            "tenant_{$this->tenantId}_min_advance_notice"       => $this->minAdvanceNotice,
            "tenant_{$this->tenantId}_email_confirmation"       => $this->emailConfirmation ? '1' : '0',
            "tenant_{$this->tenantId}_sms_reminder"             => $this->smsReminder       ? '1' : '0',
            "tenant_{$this->tenantId}_notify_owner"             => $this->notifyOwner        ? '1' : '0',
            "tenant_{$this->tenantId}_webhook_url"              => trim($this->webhookUrl),
        ];

        // Auto-generate webhook secret on first save if a URL is configured and no secret exists yet.
        if (trim($this->webhookUrl) && ! $this->webhookSecret) {
            $this->webhookSecret = \Illuminate\Support\Str::random(40);
            $behaviorSettings["tenant_{$this->tenantId}_webhook_secret"] = $this->webhookSecret;
        } elseif ($this->webhookSecret) {
            $behaviorSettings["tenant_{$this->tenantId}_webhook_secret"] = $this->webhookSecret;
        }
        foreach ($behaviorSettings as $key => $val) {
            \DB::table('settings')->updateOrInsert(['key' => $key], [
                'value'      => $val,
                'group'      => 'tenant',
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            \Illuminate\Support\Facades\Cache::forget("setting.{$key}");
        }

        $this->dispatch('form-builder-saved');
    }

    // ── plan-limit helpers ────────────────────────────────────────────

    private function isAtServiceLimit(): bool
    {
        $tenant = TenantContext::current();
        $plan   = $tenant?->plan;

        if ($plan && $plan->max_services !== null) {
            $count = \DB::table('services')->where('tenant_id', $this->tenantId)->count();
            if ($count >= $plan->max_services) {
                return true;
            }
        }

        return false;
    }

    private function isAtProviderLimit(): bool
    {
        $tenant = TenantContext::current();
        $plan   = $tenant?->plan;

        if ($plan && $plan->max_providers !== null) {
            $count = \DB::table('providers')->where('tenant_id', $this->tenantId)->count();
            if ($count >= $plan->max_providers) {
                return true;
            }
        }

        return false;
    }

    private function planLimitMessage(string $type): string
    {
        $tenant   = TenantContext::current();
        $plan     = $tenant?->plan;
        $planName = $plan?->name ?? __('your current plan');

        if ($type === 'service') {
            $limit = $plan?->max_services ?? 1;
            return __(':plan allows up to :limit service(s). Upgrade to Pro for unlimited services.', ['plan' => $planName, 'limit' => $limit]);
        }

        $limit = $plan?->max_providers ?? 1;
        return __(':plan allows up to :limit provider(s). Upgrade to Pro for unlimited providers.', ['plan' => $planName, 'limit' => $limit]);
    }

    // ── service modal ─────────────────────────────────────────────────

    public function openServiceCreate(): void
    {
        if ($this->isAtServiceLimit()) {
            $this->dispatch('toast-warning', message: $this->planLimitMessage('service'));
            return;
        }

        $this->editingServiceId = null;
        $this->svcName          = '';
        $this->svcDescription   = '';
        $this->svcCategory      = '';
        $this->svcDuration      = 30;
        $this->svcPrice         = 0;
        $this->svcColor         = '#6d28d9';
        $this->svcActive        = true;
        $this->svcSortOrder     = 0;
        $this->svcProviderIds   = [];
        $this->showServiceModal = true;
    }

    public function openServiceEdit(int $id): void
    {
        $svc = Service::find($id);
        if (! $svc || $svc->tenant_id !== $this->tenantId) {
            return;
        }

        $this->editingServiceId = $id;
        $this->svcName          = $svc->name;
        $this->svcDescription   = $svc->description ?? '';
        $this->svcCategory      = $svc->category ?? '';
        $this->svcDuration      = $svc->duration_minutes;
        $this->svcPrice         = (float) $svc->price;
        $this->svcColor         = $svc->color ?? '#6d28d9';
        $this->svcActive        = (bool) $svc->is_active;
        $this->svcSortOrder     = $svc->sort_order ?? 0;
        $this->svcProviderIds   = $svc->providers()->pluck('providers.id')->map(fn ($v) => (int) $v)->toArray();
        $this->showServiceModal = true;
    }

    public function toggleSvcProvider(int $id): void
    {
        if (in_array($id, $this->svcProviderIds, true)) {
            $this->svcProviderIds = array_values(array_filter($this->svcProviderIds, fn ($v) => $v !== $id));
        } else {
            $this->svcProviderIds[] = $id;
        }
    }

    public function saveService(): void
    {
        if (empty(trim($this->svcName))) {
            $this->svcError = 'Service name is required.';
            return;
        }
        $this->svcError = '';

        // Double-check plan limit on new services (guards direct wire: calls)
        if (! $this->editingServiceId && $this->isAtServiceLimit()) {
            $this->dispatch('toast-warning', message: $this->planLimitMessage('service'));
            return;
        }

        $data = [
            'tenant_id'        => $this->tenantId,
            'name'             => trim($this->svcName),
            'description'      => trim($this->svcDescription),
            'category'         => trim($this->svcCategory) ?: null,
            'duration_minutes' => max(5, (int) $this->svcDuration),
            'price'            => max(0, (float) $this->svcPrice),
            'color'            => $this->svcColor,
            'is_active'        => $this->svcActive,
            'sort_order'       => (int) $this->svcSortOrder,
            'currency'         => Tenant::find($this->tenantId)?->currency ?? 'USD',
        ];

        if ($this->editingServiceId) {
            \DB::table('services')
                ->where('id', $this->editingServiceId)
                ->where('tenant_id', $this->tenantId)
                ->update(array_merge($data, ['updated_at' => now()]));

            $serviceId = $this->editingServiceId;
        } else {
            $serviceId = \DB::table('services')->insertGetId(array_merge($data, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Sync provider assignments via pivot table
        \DB::table('provider_services')->where('service_id', $serviceId)->delete();
        foreach ($this->svcProviderIds as $providerId) {
            \DB::table('provider_services')->insert([
                'service_id' => $serviceId,
                'provider_id'  => $providerId,
            ]);
        }

        $this->showServiceModal = false;
        $this->dispatch('form-builder-saved');
    }

    public function toggleServiceActive(int $id): void
    {
        $svc = \DB::table('services')->where('id', $id)->where('tenant_id', $this->tenantId)->first();
        if (! $svc) {
            return;
        }
        \DB::table('services')->where('id', $id)->update([
            'is_active'  => ! $svc->is_active,
            'updated_at' => now(),
        ]);
        $this->dispatch('form-builder-saved');
    }

    public function reorderServices(array $ids): void
    {
        $ownIds = \DB::table('services')
            ->where('tenant_id', $this->tenantId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // Only process IDs that belong to this tenant
        $ids = array_values(array_filter($ids, fn ($id) => in_array((int) $id, $ownIds, true)));

        foreach ($ids as $order => $id) {
            \DB::table('services')
                ->where('id', $id)
                ->where('tenant_id', $this->tenantId)
                ->update(['sort_order' => $order, 'updated_at' => now()]);
        }
        $this->dispatch('form-builder-saved');
    }

    public function confirmDeleteService(int $id): void
    {
        $svc = \DB::table('services')->where('id', $id)->where('tenant_id', $this->tenantId)->first();
        if (! $svc) {
            return;
        }
        $this->deleteServiceId   = $id;
        $this->deleteServiceName = $svc->name;
        $this->showDeleteConfirm = true;
    }

    public function cancelDeleteService(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteServiceId   = null;
        $this->deleteServiceName = '';
    }

    public function deleteService(int $id): void
    {
        \DB::table('services')->where('id', $id)->where('tenant_id', $this->tenantId)->delete();
        $this->showDeleteConfirm = false;
        $this->deleteServiceId   = null;
        $this->deleteServiceName = '';
        $this->dispatch('form-builder-saved');
    }

    public function closeServiceModal(): void
    {
        $this->showServiceModal = false;
    }

    // ── provider modal ────────────────────────────────────────────────

    public function openProviderCreate(): void
    {
        if ($this->isAtProviderLimit()) {
            $this->dispatch('toast-warning', message: $this->planLimitMessage('provider'));
            return;
        }

        $this->editingProviderId = null;
        $this->prvUserId         = null;
        $this->prvName           = '';
        $this->prvEmail          = '';
        $this->prvJobTitle       = '';
        $this->prvExperience     = '';
        $this->prvBio            = '';
        $this->prvColor          = '#7c3aed';
        $this->prvActive         = true;
        $this->prvServiceIds     = [];
        $this->showProviderModal = true;
    }

    public function openProviderEdit(int $id): void
    {
        $prov = Provider::find($id);
        if (! $prov || $prov->tenant_id !== $this->tenantId) {
            return;
        }

        $this->editingProviderId = $id;
        $this->prvUserId         = $prov->user_id;
        $this->prvName           = $prov->user?->name ?? '';
        $this->prvEmail          = $prov->user?->email ?? '';
        $this->prvJobTitle       = $prov->job_title ?? '';
        $this->prvExperience     = $prov->experience_years ? (string) $prov->experience_years : '';
        $this->prvBio            = $prov->bio ?? '';
        $this->prvColor          = $prov->color ?? '#7c3aed';
        $this->prvActive         = (bool) ($prov->is_active ?? true);
        $this->prvServiceIds     = $prov->services()->pluck('services.id')->map(fn ($v) => (int) $v)->toArray();
        $this->showProviderModal = true;
    }

    public function toggleProvService(int $id): void
    {
        if (in_array($id, $this->prvServiceIds, true)) {
            $this->prvServiceIds = array_values(array_filter($this->prvServiceIds, fn ($v) => $v !== $id));
        } else {
            $this->prvServiceIds[] = $id;
        }
    }

    public function saveProvider(): void
    {
        if (! $this->prvUserId && ! $this->editingProviderId) {
            $this->prvError = 'Please select a team member.';
            return;
        }
        $this->prvError = '';

        // Double-check plan limit on new providers (guards direct wire: calls)
        if (! $this->editingProviderId && $this->isAtProviderLimit()) {
            $this->dispatch('toast-warning', message: $this->planLimitMessage('provider'));
            return;
        }

        $data = [
            'tenant_id'        => $this->tenantId,
            'job_title'        => trim($this->prvJobTitle),
            'experience_years' => $this->prvExperience !== '' ? (int) $this->prvExperience : null,
            'bio'              => trim($this->prvBio),
            'color'            => $this->prvColor,
            'is_active'        => $this->prvActive,
        ];

        if ($this->editingProviderId) {
            \DB::table('providers')
                ->where('id', $this->editingProviderId)
                ->where('tenant_id', $this->tenantId)
                ->update(array_merge($data, ['updated_at' => now()]));

            // Update user name/email
            if ($this->prvUserId) {
                \DB::table('users')->where('id', $this->prvUserId)->update([
                    'name'  => trim($this->prvName),
                    'email' => trim($this->prvEmail),
                ]);
            }

            $providerId = $this->editingProviderId;
        } else {
            $providerId = \DB::table('providers')->insertGetId(array_merge($data, [
                'user_id'    => $this->prvUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Sync service assignments
        \DB::table('provider_services')->where('provider_id', $providerId)->delete();
        foreach ($this->prvServiceIds as $serviceId) {
            \DB::table('provider_services')->insert([
                'provider_id'  => $providerId,
                'service_id' => $serviceId,
            ]);
        }

        $this->showProviderModal = false;
        $this->dispatch('form-builder-saved');
    }

    public function confirmDeleteProvider(int $id): void
    {
        $prov = \DB::table('providers')
            ->join('users', 'users.id', '=', 'providers.user_id')
            ->where('providers.id', $id)
            ->where('providers.tenant_id', $this->tenantId)
            ->select('providers.id', 'users.name')
            ->first();

        if (! $prov) {
            return;
        }

        $this->deleteProviderId          = $id;
        $this->deleteProviderName        = $prov->name;
        $this->showDeleteProviderConfirm = true;
    }

    public function cancelDeleteProvider(): void
    {
        $this->showDeleteProviderConfirm = false;
        $this->deleteProviderId          = null;
        $this->deleteProviderName        = '';
    }

    public function deleteProvider(int $id): void
    {
        \DB::table('providers')->where('id', $id)->where('tenant_id', $this->tenantId)->delete();
        $this->showProviderModal         = false;
        $this->editingProviderId         = null;
        $this->showDeleteProviderConfirm = false;
        $this->deleteProviderId          = null;
        $this->deleteProviderName        = '';
        $this->dispatch('form-builder-saved');
    }

    public function closeProviderModal(): void
    {
        $this->showProviderModal = false;
        $this->editingProviderId = null;
    }

    // ── render ───────────────────────────────────────────────────────

    public function render()
    {
        $tenant        = Tenant::find($this->tenantId);
        $services      = $tenant?->services()->withCount('providers')->orderBy('sort_order')->get() ?? collect();
        $providers     = $tenant?->providers()->with(['user', 'services'])->withCount('services')->get() ?? collect();
        $tenantSlug    = $tenant?->slug ?? '';
        $bookingUrl    = $tenantSlug ? url('/' . $tenantSlug) : null;
        $requiredCount = count(array_filter($this->fields, fn ($f) => $f['required']));
        // All users in this tenant
        $allUsers      = $this->tenantId
            ? \App\Models\User::where('tenant_id', $this->tenantId)->orderBy('name')->get()
            : collect();

        // For the "select a user" dropdown: exclude users already assigned to another provider.
        // When editing, keep the current provider's own user selectable.
        $takenUserIds  = $this->tenantId
            ? \App\Models\Provider::where('tenant_id', $this->tenantId)
                ->when($this->editingProviderId, fn ($q) => $q->where('id', '!=', $this->editingProviderId))
                ->pluck('user_id')
            : collect();
        $availableUsers = $allUsers->whereNotIn('id', $takenUserIds->all());

        static $tzList = null;
        $tzList    ??= \DateTimeZone::listIdentifiers();
        $timezones   = collect($tzList)->mapWithKeys(fn ($tz) => [$tz => $tz])->toArray();

        return view('livewire.tenant.form-builder', [
            'selectedField' => collect($this->fields)->firstWhere('id', $this->selectedId),
            'fieldCount'    => count($this->fields),
            'requiredCount' => $requiredCount,
            'canUndo'       => ! empty($this->history),
            'canRedo'       => ! empty($this->future),
            'services'      => $services,
            'providers'     => $providers,
            'allUsers'      => $allUsers,
            'availableUsers' => $availableUsers,
            'bookingUrl'    => $bookingUrl,
            'tenantSlug'    => $tenantSlug,
            'timezones'     => $timezones,
            'fieldTypes'    => [
                ['key' => 'short_text',  'label' => __('Short text'),  'color' => '#6366f1'],
                ['key' => 'email',       'label' => __('Email'),        'color' => '#3b82f6'],
                ['key' => 'phone',       'label' => __('Phone'),        'color' => '#10b981'],
                ['key' => 'dropdown',    'label' => __('Dropdown'),     'color' => '#8b5cf6'],
                ['key' => 'date_picker', 'label' => __('Date picker'),  'color' => '#f59e0b'],
                ['key' => 'time_slot',   'label' => __('Time slot'),    'color' => '#06b6d4'],
                ['key' => 'file_upload', 'label' => __('File upload'),  'color' => '#ec4899'],
                ['key' => 'checkbox',    'label' => __('Checkbox'),     'color' => '#10b981'],
                ['key' => 'radio_group', 'label' => __('Radio group'),  'color' => '#3b82f6'],
                ['key' => 'signature',   'label' => __('Signature'),    'color' => '#ef4444'],
            ],
        ]);
    }
}
