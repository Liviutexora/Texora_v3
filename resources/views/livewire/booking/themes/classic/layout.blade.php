{{-- Classic theme: clean single-column card --}}
<div class="booking-card bw-card-shadow overflow-hidden rounded-2xl border border-[var(--bw-border)] bg-[var(--bw-bg)]"
     data-force-dark="{{ ($forceDarkMode ?? false) ? '1' : '0' }}"
     data-match-system="{{ ($matchSystemTheme ?? true) ? '1' : '0' }}">
@php
    $btnRadius = match($buttonStyle ?? 'rounded') {
        'pill'  => '999px',
        'sharp' => '4px',
        default => '10px',
    };
@endphp

    {{-- ── Card header ───────────────────────────────────────── --}}
    @if ($step < 6)
    @php
        // Resolve selected provider name for header pills
        $selProviderName = null;
        if ($providerId) {
            $pList = $this->getProviders();
            $selProviderName = $pList->firstWhere('id', $providerId)?->user?->name;
        }
    @endphp
    <div class="px-7 pt-6 pb-[18px] border-b border-[var(--bw-border)]">
        {{-- Business logo / monogram + name row --}}
        <div class="flex items-center gap-[10px] mb-2">
            @if ($tenant?->logo)
                <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}"
                     class="h-7 w-auto max-w-[80px] rounded-[6px] object-contain shrink-0">
            @else
                @php
                    $initials = collect(explode(' ', $tenant?->name ?? 'S'))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
                @endphp
                <div class="w-7 h-7 rounded-lg text-[11px] font-bold text-white flex items-center justify-content shrink-0 justify-center"
                     style="background: linear-gradient(135deg, {{ $brand }}, color-mix(in srgb, {{ $brand }} 65%, #000));">
                    {{ $initials }}
                </div>
            @endif
            <div class="text-[12px] font-semibold tracking-[0.2px] text-[var(--bw-text-4)]">
                {{ $tenant?->name ?? config('app.name') }}
            </div>

            {{-- Contact pills + language switcher --}}
            @php
                $langMeta = [
                    'en' => ['flag' => '🇬🇧', 'label' => 'EN'],
                    'ro' => ['flag' => '🇷🇴', 'label' => 'RO'],
                    'es' => ['flag' => '🇪🇸', 'label' => 'ES'],
                    'de' => ['flag' => '🇩🇪', 'label' => 'DE'],
                    'fr' => ['flag' => '🇫🇷', 'label' => 'FR'],
                    'ar' => ['flag' => '🇸🇦', 'label' => 'AR'],
                    'ru' => ['flag' => '🇷🇺', 'label' => 'RU'],
                    'zh' => ['flag' => '🇨🇳', 'label' => 'ZH'],
                    'hi' => ['flag' => '🇮🇳', 'label' => 'HI'],
                ];
                $enabledLocales = \App\Http\Middleware\SetLocale::enabledLocales();
                $currentLocale  = app()->getLocale();
                $showLangPicker = count($enabledLocales) > 1;
            @endphp
            <div class="flex items-center gap-[10px] ml-auto flex-wrap">
                @if($tenantPhone)
                <a href="tel:{{ $tenantPhone }}"
                   class="inline-flex items-center gap-1 text-[11px] no-underline text-[var(--bw-text-3)]"
                   onmouseover="this.style.color='{{ $brand }}'" onmouseout="this.style.color='var(--bw-text-3)'">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.57 3.41 2 2 0 0 1 3.54 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.59a16 16 0 0 0 6 6l.9-.9a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    {{ $tenantPhone }}
                </a>
                @endif
                @if($tenantWebsite)
                <a href="{{ $tenantWebsite }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 text-[11px] no-underline text-[var(--bw-text-3)]"
                   onmouseover="this.style.color='{{ $brand }}'" onmouseout="this.style.color='var(--bw-text-3)'">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    {{ parse_url($tenantWebsite, PHP_URL_HOST) ?: $tenantWebsite }}
                </a>
                @endif

                {{-- Language picker (only shown when >1 language is enabled) --}}
                @if($showLangPicker)
                <div style="position:relative;" x-data="{ open: false }" @click.outside="open = false">
                    {{-- Trigger button --}}
                    <button @click="open = !open"
                            style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px 3px 6px;border:1px solid var(--bw-border);border-radius:6px;background:transparent;cursor:pointer;font-family:inherit;transition:border-color .12s,background .12s;"
                            :style="open ? 'border-color:{{ $brand }};background:{{ $brandLight }}' : ''"
                            onmouseover="if(!this.parentElement.querySelector('[x-bind]')?.__x?.data?.open) { this.style.borderColor='{{ $brand }}'; this.style.background='{{ $brandLight }}'; }"
                            onmouseout="if(!this.parentElement.querySelector('[x-bind]')?.__x?.data?.open) { this.style.borderColor='var(--bw-border)'; this.style.background='transparent'; }">
                        <span style="font-size:14px;line-height:1;">{{ $langMeta[$currentLocale]['flag'] ?? '🌐' }}</span>
                        <span style="font-size:11px;font-weight:600;color:var(--bw-text-3);letter-spacing:0.03em;">{{ $langMeta[$currentLocale]['label'] ?? strtoupper($currentLocale) }}</span>
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--bw-text-4);transition:transform .15s;" :style="open ? 'transform:rotate(180deg)' : ''">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         style="position:absolute;top:calc(100% + 6px);right:0;z-index:50;min-width:130px;background:var(--bw-bg);border:1px solid var(--bw-border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12),0 2px 6px rgba(0,0,0,0.06);overflow:hidden;transform-origin:top right;"
                         @click.stop>
                        @foreach($enabledLocales as $loc)
                        @php $meta = $langMeta[$loc] ?? ['flag' => '🌐', 'label' => strtoupper($loc)]; @endphp
                        <form method="POST" action="{{ route('locale.switch') }}" style="margin:0;">
                            @csrf
                            <input type="hidden" name="locale" value="{{ $loc }}">
                            <button type="submit"
                                    style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 12px;border:none;background:{{ $loc === $currentLocale ? $brandLight : 'transparent' }};cursor:pointer;font-family:inherit;text-align:left;transition:background .1s;"
                                    onmouseover="this.style.background='{{ $brandLight }}'"
                                    onmouseout="this.style.background='{{ $loc === $currentLocale ? $brandLight : 'transparent' }}'">
                                <span style="font-size:15px;line-height:1;flex-shrink:0;">{{ $meta['flag'] }}</span>
                                <span style="font-size:12px;font-weight:{{ $loc === $currentLocale ? '700' : '500' }};color:{{ $loc === $currentLocale ? $brand : 'var(--bw-text-2)' }};">
                                    {{ $meta['label'] }}
                                </span>
                                @if($loc === $currentLocale)
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="{{ $brand }}" stroke-width="2.5" style="margin-left:auto;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Tagline --}}
        @if($tenantTagline)
        <div class="text-[12px] text-[var(--bw-text-6)] mb-[6px] leading-[1.4]">{{ $tenantTagline }}</div>
        @endif

        @php
            $headerTitle = __('Book your appointment');
            $headerDuration = null;
            if ($step > 1) {
                if ($allowMultipleServices && count($activeServiceIds) > 1) {
                    $selectedServices = $services->whereIn('id', $activeServiceIds);
                    $headerTitle    = $selectedServices->map(fn ($s) => $s->name . ' (' . $s->duration_minutes . ' min)')->implode(', ');
                    $headerDuration = $selectedServices->sum('duration_minutes');
                } else {
                    $headerTitle    = $selectedService?->name;
                    $headerDuration = $selectedService?->duration_minutes;
                }
            }
        @endphp
        <div class="text-[22px] font-semibold tracking-[-0.3px] text-[var(--bw-text)]">
            {{ $headerTitle }}
        </div>

        {{-- Contextual summary pills --}}
        <div class="flex flex-wrap gap-[14px] mt-2 text-[12px] text-[var(--bw-text-3)]">
            @if ($headerDuration)
                <span class="inline-flex items-center gap-1">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ $headerDuration }} min
                </span>
            @endif
            @if ($selProviderName)
                <span class="inline-flex items-center gap-1">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    {{ $selProviderName }}
                </span>
            @endif
            @if ($selectedDate)
                <span class="inline-flex items-center gap-1">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                    {{ \Carbon\Carbon::parse($selectedDate)->format('D, d M') }}
                    @if ($selectedStart) · {{ $selectedStart }} @endif
                </span>
            @endif
        </div>
    </div>

    {{-- ── Stepper progress bars ─────────────────────────────── --}}
    <div class="flex gap-[6px] px-7 pt-[14px]">
        @foreach ($stepLabels as $i => $label)
            @php
                $n = $i + 1;
                $isActive = $currentStep === $n;
                $isDone   = $currentStep > $n;
            @endphp
            <div class="flex-1 min-w-0">
                <div class="h-[3px] rounded-[2px] transition-[background] duration-[250ms]"
                     style="background: {{ ($isDone || $isActive) ? $brand : 'var(--bw-border)' }};
                            opacity: {{ ($isDone || $isActive) ? '1' : '0.5' }};"></div>
                <div @class(['hidden sm:block text-[11px] mt-[6px]',
                             'font-semibold text-[var(--bw-text)]'   => $isActive,
                             'font-normal text-[var(--bw-text-3)]'   => !$isActive])>
                    <span class="text-[var(--bw-text-5)] mr-[3px]">{{ $n }}</span>{{ $label }}
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- ── Step content area ─────────────────────────────────── --}}
    <div @class(['px-7 pt-7 pb-8 min-h-[360px]' => $step < 6])

        {{-- ── Step 1: Service ───────────────────────────────── --}}
        @if ($step === 1)
        <div>
            <p class="text-[13px] text-[var(--bw-text-3)] mb-[14px]">
                {{ $allowMultipleServices ? __("Select one or more services you'd like to book.") : __("Select the service you'd like to book.") }}
            </p>

            @php
                $totalDuration = 0;
                $totalPrice    = 0;
            @endphp

            <div class="flex flex-col gap-2">
                @forelse ($services as $service)
                @php
                    $sel = $allowMultipleServices
                        ? in_array($service->id, $activeServiceIds, true)
                        : ($serviceId === $service->id);
                    if ($sel) {
                        $totalDuration += $service->duration_minutes;
                        $totalPrice    += (float) $service->price;
                    }
                @endphp
                <button wire:click="selectService({{ $service->id }})"
                    class="flex items-start gap-[14px] text-left p-4 cursor-pointer w-full font-[inherit] rounded-[10px] transition-all duration-[120ms]"
                    style="border:1.5px solid {{ $sel ? $brand : 'var(--bw-border)' }};
                           background:{{ $sel ? $brandLight : 'var(--bw-bg)' }};">

                    @if ($allowMultipleServices)
                    {{-- Checkbox --}}
                    <div class="w-[18px] h-[18px] rounded-[5px] mt-[2px] shrink-0 flex items-center justify-center"
                         style="border:2px solid {{ $sel ? $brand : 'var(--bw-border)' }};
                                background:{{ $sel ? $brand : 'transparent' }};">
                        @if ($sel)
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @endif
                    </div>
                    @else
                    {{-- Radio dot --}}
                    <div class="w-[18px] h-[18px] rounded-[9px] mt-[2px] shrink-0 flex items-center justify-center"
                         style="border:2px solid {{ $sel ? $brand : 'var(--bw-border)' }};
                                background:{{ $sel ? $brand : 'transparent' }};">
                        @if ($sel)
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @endif
                    </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between gap-3">
                            <div class="text-[14px] font-semibold text-[var(--bw-text)]">{{ $service->name }}</div>
                            <div class="text-[13px] font-semibold text-[var(--bw-text-2)] shrink-0">{{ $service->price_formatted }}</div>
                        </div>
                        @if ($service->description)
                        <div class="text-[12px] text-[var(--bw-text-3)] mt-1">{{ $service->description }}</div>
                        @endif
                        <div class="text-[11px] text-[var(--bw-text-5)] mt-[6px] inline-flex items-center gap-1">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $service->duration_minutes }} min
                        </div>
                    </div>
                </button>
                @empty
                <p class="text-[var(--bw-text-3)] text-center py-8">{{ __('No services available yet.') }}</p>
                @endforelse
            </div>

            {{-- Multi-select summary bar --}}
            @if ($allowMultipleServices && count($activeServiceIds) > 1)
            <div class="mt-3 px-[14px] py-[10px] rounded-lg flex justify-between items-center text-[12px] text-[var(--bw-text)]"
                 style="background:{{ $brandLight }}; border:1px solid {{ $brandBorder }};">
                <span><strong>{{ count($activeServiceIds) }} {{ __('services selected') }}</strong></span>
                <span>{{ $totalDuration }} min
                    @if($totalPrice > 0) · {{ $tenant?->currency ?? '' }} {{ number_format($totalPrice, 2) }} @endif
                </span>
            </div>
            @endif

            {{-- Continue button --}}
            <div class="mt-[14px]">
                @if ($serviceId)
                <button wire:click="continueFromService"
                    class="w-full px-5 py-[13px] text-[14px] font-semibold font-[inherit] border-none text-white cursor-pointer transition-opacity duration-[150ms] flex items-center justify-center gap-2 hover:opacity-[.88]"
                    style="border-radius:{{ $btnRadius }}; background:{{ $brand }};">
                    <span>{{ __('Continue') }} @if($allowMultipleServices && count($activeServiceIds) > 1)({{ count($activeServiceIds) }}) @endif</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
                @else
                <button disabled
                    class="w-full px-5 py-[13px] text-[14px] font-semibold font-[inherit] border-none rounded-[10px] bg-[var(--bw-disabled-bg)] text-[var(--bw-text-6)] cursor-not-allowed flex items-center justify-center">
                    {{ $allowMultipleServices ? __('Select at least one service to continue') : __('Select a service to continue') }}
                </button>
                @endif
            </div>
        </div>
        @endif

        {{-- ── Step 2: Provider ──────────────────────────────── --}}
        @if ($step === 2)
        <div>
            <p class="text-[13px] text-[var(--bw-text-3)] mb-[14px]">{{ __('Choose your provider for this appointment.') }}</p>
            <div class="cls-provider-grid grid gap-[10px]">

                {{-- No preference card --}}
                @php
                    $noSel = $providerId === null;
                @endphp
                <button wire:click="selectProvider(0)"
                    class="flex items-center gap-3 p-[14px] rounded-[10px] text-left cursor-pointer font-[inherit] transition-all duration-[120ms] w-full"
                    style="border:{{ $noSel ? '2px' : '1px' }} solid {{ $noSel ? $brand : 'var(--bw-border)' }};
                           background:{{ $noSel ? $brandLight : 'var(--bw-bg)' }};">
                    <div class="w-[42px] h-[42px] rounded-[21px] shrink-0 bg-[var(--bw-bg-raised)] flex items-center justify-center"
                         class="bw-avatar-dashed">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a1a1aa" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-semibold text-[var(--bw-text)]">{{ __('No preference') }}</div>
                        <div class="text-[11px] text-[var(--bw-text-3)] mt-[2px]">{{ __('Any available provider') }}</div>
                    </div>
                    @if ($noSel)
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $brand }}" stroke="none"><circle cx="12" cy="12" r="12"/><polyline points="7 12 10 15 17 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                    @endif
                </button>

                {{-- Provider cards --}}
                @forelse ($providers as $provider)
                @php
                    $sel = $providerId === $provider->id;
                    $colors = ['#fbbf24','#a78bfa','#34d399','#60a5fa','#f87171','#fb923c'];
                    $colorIdx = crc32($provider->user->name ?? 'P') % count($colors);
                    $avatarColor = $colors[abs($colorIdx)];
                    $provInitials = collect(explode(' ', $provider->user->name ?? 'P'))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
                @endphp
                <button wire:click="selectProvider({{ $provider->id }})"
                    class="flex items-center gap-3 p-[14px] rounded-[10px] text-left cursor-pointer font-[inherit] transition-all duration-[120ms] w-full"
                    style="border:{{ $sel ? '2px' : '1px' }} solid {{ $sel ? $brand : 'var(--bw-border)' }};
                           background:{{ $sel ? $brandLight : 'var(--bw-bg)' }};">
                    <div class="w-[42px] h-[42px] rounded-[21px] shrink-0 text-white font-bold flex items-center justify-center text-[14px]"
                         style="background:{{ $avatarColor }};">
                        {{ $provInitials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-semibold text-[var(--bw-text)]">{{ $provider->user->name }}</div>
                        @if ($provider->job_title)
                        <div class="text-[11px] text-[var(--bw-text-3)] mt-[2px]">{{ $provider->job_title }}</div>
                        @endif
                        @if ($provider->experience_years)
                        <div class="text-[11px] text-[var(--bw-text-5)] mt-[2px]">{{ trans_choice(':count year experience|:count years experience', $provider->experience_years, ['count' => $provider->experience_years]) }}</div>
                        @endif
                    </div>
                    @if ($sel)
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $brand }}" stroke="none"><circle cx="12" cy="12" r="12"/><polyline points="7 12 10 15 17 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                    @endif
                </button>
                @empty
                <p class="text-[var(--bw-text-3)] col-span-full text-center py-8">{{ __('No providers available for this service.') }}</p>
                @endforelse
            </div>
        </div>
        @endif

        {{-- ── Step 3: Date & Time ───────────────────────────── --}}
        @if ($step === 3)
        @php
            $calToday   = now()->toDateString();
            $calMax     = now()->addMonths(3)->toDateString();
            $calInitY   = $selectedDate ? \Carbon\Carbon::parse($selectedDate)->year  : now()->year;
            $calInitM   = $selectedDate ? (\Carbon\Carbon::parse($selectedDate)->month - 1) : (now()->month - 1);

            $dayKeyToJs = ['sun'=>0,'mon'=>1,'tue'=>2,'wed'=>3,'thu'=>4,'fri'=>5,'sat'=>6];
            $calClosedDays = collect($tenantAvailability ?? [])
                ->filter(fn($d) => empty($d['enabled']))
                ->keys()
                ->map(fn($k) => $dayKeyToJs[$k] ?? null)
                ->filter(fn($v) => $v !== null)
                ->values()
                ->toJson();
        @endphp
        <div>
            <p class="text-[13px] text-[var(--bw-text-3)] mb-[18px]">{{ __('Choose a date and available time slot.') }}</p>

        @if (($datePickerStyle ?? 'monthly') === 'weekly')
            {{-- ── Weekly strip ──────────────────────────────── --}}
            @include('livewire.booking.partials.date-picker-weekly')

            {{-- Slots below the strip --}}
            <div class="mt-4">
                <div class="text-[11px] font-semibold text-[var(--bw-text-3)] tracking-[0.4px] uppercase mb-2">
                    @if ($selectedDate)
                        {{ \Carbon\Carbon::parse($selectedDate)->format('D, d M') }}
                    @else
                        {{ __('Available times') }}
                    @endif
                </div>
                @if ($selectedDate)
                    @if ($availableSlots->isEmpty())
                        <div class="text-[12px] text-[var(--bw-text-3)] border border-dashed border-[var(--bw-border)] py-5 text-center rounded-lg">
                            {{ __('No slots on this date.') }}<br>{{ __('Try another day.') }}
                        </div>
                    @else
                        <div class="grid grid-cols-4 gap-[5px]">
                            @foreach ($availableSlots as $slot)
                            @php $slotSel = $selectedStart === $slot['start']; @endphp
                            <button wire:click="selectSlot('{{ $slot['start'] }}', '{{ $slot['end'] }}')"
                                class="py-[9px] px-1 text-[12px] font-[inherit] cursor-pointer rounded-[7px] transition-all duration-[120ms] text-center"
                                style="font-weight:{{ $slotSel ? '600' : '500' }};
                                       border:1px solid {{ $slotSel ? $brand : 'var(--bw-border)' }};
                                       background:{{ $slotSel ? $brand : 'var(--bw-bg)' }};
                                       color:{{ $slotSel ? '#fff' : 'var(--bw-text)' }};">
                                {{ $slot['start'] }}
                            </button>
                            @endforeach
                        </div>
                        <div class="mt-[10px] px-[10px] py-[7px] rounded-[6px] text-[11px] text-[var(--bw-text-3)] flex items-center gap-[5px] bg-[var(--bw-bg-muted)]">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $tenant?->timezone ?? 'UTC' }}
                        </div>
                    @endif
                @else
                    <div class="text-[12px] text-[var(--bw-text-5)] border border-dashed border-[var(--bw-border)] py-4 px-2 rounded-lg text-center">
                        ← {{ __('Pick a date') }}
                    </div>
                @endif
            </div>
        @else
            <div class="cls-date-grid grid gap-5 items-start">

                {{-- ── Alpine calendar ──────────────────────────── --}}
                <div x-data="{
                    today:      '{{ $calToday }}',
                    maxDate:    '{{ $calMax }}',
                    selected:   '{{ $selectedDate ?? '' }}',
                    viewYear:   {{ $calInitY }},
                    viewMonth:  {{ $calInitM }},
                    closedDays: {{ $calClosedDays }},
                    dow: ['Su','Mo','Tu','We','Th','Fr','Sa'],

                    get monthLabel() {
                        return new Date(this.viewYear, this.viewMonth, 1)
                            .toLocaleString('default', { month:'long', year:'numeric' });
                    },

                    get cells() {
                        let first = new Date(this.viewYear, this.viewMonth, 1);
                        let total = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                        let cells = [];
                        for (let i = 0; i < first.getDay(); i++) cells.push(null);
                        for (let d = 1; d <= total; d++) {
                            let mm = String(this.viewMonth + 1).padStart(2,'0');
                            let dd = String(d).padStart(2,'0');
                            cells.push(this.viewYear + '-' + mm + '-' + dd);
                        }
                        return cells;
                    },

                    pad(cells) {
                        let r = [...cells];
                        while (r.length % 7 !== 0) r.push(null);
                        return r;
                    },

                    canPrev() {
                        let y = this.viewMonth === 0 ? this.viewYear - 1 : this.viewYear;
                        let m = this.viewMonth === 0 ? 11 : this.viewMonth - 1;
                        let lastOfPrev = new Date(y, m + 1, 0);
                        let s = y + '-' + String(m+1).padStart(2,'0') + '-' + String(lastOfPrev.getDate()).padStart(2,'0');
                        return s >= this.today;
                    },

                    canNext() {
                        let y = this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear;
                        let m = this.viewMonth === 11 ? 0 : this.viewMonth + 1;
                        let first = y + '-' + String(m+1).padStart(2,'0') + '-01';
                        return first <= this.maxDate;
                    },

                    prevMonth() {
                        if (!this.canPrev()) return;
                        if (this.viewMonth === 0) { this.viewYear--; this.viewMonth = 11; }
                        else this.viewMonth--;
                    },

                    nextMonth() {
                        if (!this.canNext()) return;
                        if (this.viewMonth === 11) { this.viewYear++; this.viewMonth = 0; }
                        else this.viewMonth++;
                    },

                    isDisabled(d) {
                        if (!d || d < this.today || d > this.maxDate) return true;
                        if (this.closedDays.length) {
                            let dow = new Date(d + 'T00:00').getDay();
                            if (this.closedDays.includes(dow)) return true;
                        }
                        return false;
                    },
                    isSelected(d) { return d === this.selected; },
                    isToday(d)    { return d === this.today; },

                    selectDay(d) {
                        if (this.isDisabled(d)) return;
                        this.selected = d;
                        $wire.set('selectedDate', d);
                        $wire.set('selectedStart', null);
                        $wire.set('selectedEnd', null);
                    }
                }"
                class="rounded-[12px] overflow-hidden select-none border border-[var(--bw-border)]">

                    {{-- Month header --}}
                    <div class="flex items-center justify-between px-[14px] py-3 text-[var(--bw-text)] bg-[var(--bw-bg-subtle)] border-b border-[var(--bw-border-muted)]">
                        <button type="button" @click="prevMonth()"
                            :style="canPrev() ? 'opacity:1;cursor:pointer;' : 'opacity:0.25;cursor:default;'"
                            class="w-7 h-7 rounded-[6px] border border-[var(--bw-border)] bg-[var(--bw-bg)] flex items-center justify-center p-0 transition-opacity duration-[150ms]">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <span x-text="monthLabel" class="text-[13.5px] font-semibold text-[var(--bw-text)]"></span>
                        <button type="button" @click="nextMonth()"
                            :style="canNext() ? 'opacity:1;cursor:pointer;' : 'opacity:0.25;cursor:default;'"
                            class="w-7 h-7 rounded-[6px] border border-[var(--bw-border)] bg-[var(--bw-bg)] flex items-center justify-center p-0 transition-opacity duration-[150ms]">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>

                    {{-- DOW labels --}}
                    <div class="grid grid-cols-7 gap-0 px-[10px] pt-2 pb-1 bg-[var(--bw-bg-subtle)]">
                        <template x-for="d in dow" :key="d">
                            <div x-text="d" class="text-center text-[10.5px] font-semibold text-[var(--bw-text-5)] tracking-[.3px] pb-[2px]"></div>
                        </template>
                    </div>

                    {{-- Day grid --}}
                    <div class="grid grid-cols-7 gap-[2px] px-[10px] pt-1 pb-3">
                        <template x-for="(date,i) in pad(cells)" :key="i">
                            <div>
                                <button
                                    type="button"
                                    x-text="date ? new Date(date+'T00:00').getDate() : ''"
                                    @click="selectDay(date)"
                                    :disabled="isDisabled(date)"
                                    :style="`
                                        width:100%; aspect-ratio:1; border-radius:50%;
                                        font-size:12.5px; font-family:inherit;
                                        border: ${isSelected(date) ? '2px solid {{ $brand }}' : isToday(date) ? '1.5px solid {{ $brand }}' : '1px solid transparent'};
                                        background: ${isSelected(date) ? '{{ $brand }}' : 'transparent'};
                                        color: ${isSelected(date) ? '#fff' : isDisabled(date) ? 'var(--bw-text-5)' : isToday(date) ? '{{ $brand }}' : 'var(--bw-text)'};
                                        font-weight: ${isSelected(date) || isToday(date) ? '600' : '400'};
                                        cursor: ${isDisabled(date) ? 'default' : 'pointer'};
                                        transition: all .1s;
                                        display: ${date ? 'flex' : 'none'};
                                        align-items:center; justify-content:center;
                                    `"
                                    @mouseenter="if(!isDisabled(date) && !isSelected(date)) $el.style.background='{{ $brandLight }}'"
                                    @mouseleave="if(!isDisabled(date) && !isSelected(date)) $el.style.background='transparent'"
                                ></button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ── Slot grid ──────────────────────────────── --}}
                <div>
                    <div class="text-[11px] font-semibold text-[var(--bw-text-3)] tracking-[0.4px] uppercase mb-2">
                        @if ($selectedDate)
                            {{ \Carbon\Carbon::parse($selectedDate)->format('D, d M') }}
                        @else
                            {{ __('Available times') }}
                        @endif
                    </div>

                    @if ($selectedDate)
                        @if ($availableSlots->isEmpty())
                            <div class="text-[12px] text-[var(--bw-text-3)] border border-dashed border-[var(--bw-border)] py-5 text-center rounded-lg">
                                {{ __('No slots on this date.') }}<br>{{ __('Try another day.') }}
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-[5px]">
                                @foreach ($availableSlots as $slot)
                                @php $slotSel = $selectedStart === $slot['start']; @endphp
                                <button wire:click="selectSlot('{{ $slot['start'] }}', '{{ $slot['end'] }}')"
                                    class="py-[9px] px-1 text-[12px] font-[inherit] cursor-pointer rounded-[7px] transition-all duration-[120ms] text-center"
                                    style="font-weight:{{ $slotSel ? '600' : '500' }};
                                           border:1px solid {{ $slotSel ? $brand : 'var(--bw-border)' }};
                                           background:{{ $slotSel ? $brand : 'var(--bw-bg)' }};
                                           color:{{ $slotSel ? '#fff' : 'var(--bw-text)' }};">
                                    {{ $slot['start'] }}
                                </button>
                                @endforeach
                            </div>
                            <div class="mt-[10px] px-[10px] py-[7px] rounded-[6px] text-[11px] text-[var(--bw-text-3)] flex items-center gap-[5px] bg-[var(--bw-bg-muted)]">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $tenant?->timezone ?? 'UTC' }}
                            </div>
                        @endif
                    @else
                        <div class="text-[12px] text-[var(--bw-text-5)] border border-dashed border-[var(--bw-border)] py-4 px-2 rounded-lg text-center">
                            ← {{ __('Pick a date') }}
                        </div>
                    @endif
                </div>

            </div>
        @endif {{-- end monthly/weekly --}}
        </div>
        @endif

        {{-- ── Step 4: Your Details ──────────────────────────── --}}
        @if ($step === 4)
        @php $authUser = auth()->user(); @endphp
        <div>
            @if ($authUser)
            <div class="flex items-center gap-[10px] bg-[#f5f3ff] border border-[#e0d9ff] rounded-[9px] px-[14px] py-[10px] mb-4">
                <svg class="shrink-0 text-[#7c3aed]" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <div>
                    <div class="text-[12px] font-semibold text-[#5b21b6] leading-[1.3]">{{ $authUser->name }}</div>
                    <div class="text-[11px] text-[#7c3aed]">{{ $authUser->email }}</div>
                </div>
            </div>
            @else
            <p class="text-[13px] text-[var(--bw-text-3)] mb-[14px]">{{ __('Just a few details to lock in your appointment.') }}</p>
            @endif

            <div class="flex flex-col gap-[14px]">
                @if (!empty($customFields))
                    @foreach ($customFields as $idx => $field)
                    @if ($field['hidden'] ?? false) @continue @endif
                    <div>
                        <label class="block text-[13px] font-medium text-[var(--bw-text-2)] mb-[5px]">
                            {{ $field['label'] }}
                            @if ($field['required'] ?? false) <span class="text-red-600">*</span> @endif
                        </label>
                        @php $ft = $field['type'] ?? 'short_text'; @endphp
                        @if (in_array($ft, ['textarea']))
                            <textarea wire:model="customAnswers.{{ $idx }}" rows="3"
                                class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none resize-y box-border"
                                onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'"></textarea>

                        @elseif (in_array($ft, ['select', 'dropdown']))
                            <select wire:model="customAnswers.{{ $idx }}"
                                class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none box-border bg-[var(--bw-bg)]">
                                <option value="">{{ $field['placeholder'] ?? '— Select —' }}</option>
                                @foreach ($field['options'] ?? [] as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>

                        @elseif ($ft === 'radio_group')
                            <div class="flex flex-col gap-2 mt-1">
                                @foreach ($field['options'] ?? [] as $opt)
                                <label class="flex items-center gap-[9px] cursor-pointer">
                                    <input type="radio" wire:model="customAnswers.{{ $idx }}" value="{{ $opt }}"
                                        class="w-4 h-4 cursor-pointer"
                                        style="accent-color:{{ $brand }};">
                                    <span class="text-[14px] text-[var(--bw-text)]">{{ $opt }}</span>
                                </label>
                                @endforeach
                            </div>

                        @elseif ($ft === 'checkbox')
                            <label class="flex items-center gap-[9px] cursor-pointer">
                                <input type="checkbox" wire:model="customAnswers.{{ $idx }}"
                                    class="w-4 h-4 cursor-pointer"
                                    style="accent-color:{{ $brand }};">
                                <span class="text-[14px] text-[var(--bw-text)]">{{ $field['label'] }}</span>
                            </label>

                        @elseif ($ft === 'date_picker')
                            <input type="date" wire:model="customAnswers.{{ $idx }}"
                                class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none box-border"
                                onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">

                        @elseif ($ft === 'time_slot')
                            <input type="time" wire:model="customAnswers.{{ $idx }}"
                                class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none box-border"
                                onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">

                        @elseif ($ft === 'file_upload')
                            <input type="file" wire:model="customAnswers.{{ $idx }}"
                                class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-2 text-[13px] font-[inherit] text-[var(--bw-text)] outline-none box-border bg-[var(--bw-bg-raised)]">

                        @elseif ($ft === 'email')
                            @php $isAuthEmail = $authUser && strtolower($customAnswers[$idx] ?? '') === strtolower($authUser->email ?? ''); @endphp
                            <input type="email" wire:model="customAnswers.{{ $idx }}"
                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                @if($isAuthEmail) readonly @endif
                                @class(['w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none box-border',
                                        'bg-[var(--bw-bg-muted)] cursor-not-allowed' => $isAuthEmail])
                                @if(!$isAuthEmail) onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'" @endif>
                            @if($isAuthEmail)
                            <p class="text-[11px] text-[var(--bw-text-4)] mt-[3px]">{{ __('Locked to your signed-in account.') }}</p>
                            @endif

                        @elseif ($ft === 'phone')
                            <input type="tel" wire:model="customAnswers.{{ $idx }}"
                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none box-border"
                                onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">

                        @elseif ($ft === 'signature')
                            @php $sigVal = $customAnswers[$idx] ?? ''; @endphp
                            <div wire:ignore
                                 x-data="{
                                    drawing: false,
                                    hasStroke: false,
                                    init() {
                                        const canvas = this.$refs.canvas;
                                        const ctx    = canvas.getContext('2d');
                                        ctx.lineWidth = 2.5; ctx.lineCap = 'round'; ctx.lineJoin = 'round';
                                        ctx.strokeStyle = '#333';

                                        const saved = canvas.dataset.saved;
                                        if (saved && saved.startsWith('data:image')) {
                                            this.hasStroke = true;
                                            const img = new Image();
                                            img.onload = () => ctx.drawImage(img, 0, 0);
                                            img.src = saved;
                                        }

                                        const pos = (e) => {
                                            const r = canvas.getBoundingClientRect();
                                            const s = e.touches ? e.touches[0] : e;
                                            return { x: (s.clientX - r.left) * (canvas.width / r.width),
                                                     y: (s.clientY - r.top)  * (canvas.height / r.height) };
                                        };
                                        const start = (e) => { e.preventDefault(); this.drawing = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); };
                                        const move  = (e) => { e.preventDefault(); if (!this.drawing) return; const p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); this.hasStroke = true; };
                                        const end   = () => {
                                            if (!this.drawing) return;
                                            this.drawing = false;
                                            const url = canvas.toDataURL();
                                            canvas.dataset.saved = url;
                                            this.$wire.setCustomAnswer({{ $idx }}, url);
                                        };
                                        canvas.addEventListener('mousedown',  start);
                                        canvas.addEventListener('mousemove',  move);
                                        canvas.addEventListener('mouseup',    end);
                                        canvas.addEventListener('touchstart', start, { passive: false });
                                        canvas.addEventListener('touchmove',  move,  { passive: false });
                                        canvas.addEventListener('touchend',   end);
                                    },
                                    clear() {
                                        const canvas = this.$refs.canvas;
                                        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                                        canvas.dataset.saved = '';
                                        this.hasStroke = false;
                                        this.$wire.setCustomAnswer({{ $idx }}, '');
                                    }
                                }">
                                <div class="relative rounded-lg overflow-hidden border border-[var(--bw-border)] bg-[var(--bw-bg)]">
                                    <canvas x-ref="canvas" width="600" height="160"
                                        data-saved="{{ $sigVal }}"
                                        class="w-full h-[120px] block cursor-crosshair touch-none"></canvas>
                                    <div class="absolute top-[6px] left-[10px] text-[10px] text-[var(--bw-text-5)] pointer-events-none"
                                         x-show="!hasStroke">{{ __('Sign here') }} ↓</div>
                                    <button type="button" x-show="hasStroke" @click="clear()"
                                        class="absolute top-[6px] right-2 text-[11px] px-2 py-[2px] rounded-[5px] border border-[var(--bw-border)] bg-[var(--bw-bg-muted)] text-[var(--bw-text-3)] cursor-pointer">
                                        {{ __('Clear') }}
                                    </button>
                                </div>
                            </div>

                        @else
                            <input type="text" wire:model="customAnswers.{{ $idx }}"
                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none box-border"
                                onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">
                        @endif
                        @error("customAnswers.$idx") <span class="text-[11px] text-red-600 mt-[3px] block">{{ $message }}</span> @enderror
                    </div>
                    @endforeach
                @else
                <div>
                    <label class="block text-[13px] font-medium text-[var(--bw-text-2)] mb-[5px]">
                        {{ __('Full Name') }} <span class="text-red-600">*</span>
                    </label>
                    <input type="text" wire:model="name" placeholder="{{ __('Jane Doe') }}"
                        class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none transition-[border] duration-[150ms] box-border"
                        onfocus="this.style.borderColor='{{ $brand }}'; this.style.boxShadow='0 0 0 3px {{ $brandLight }}';"
                        onblur="this.style.borderColor='var(--bw-border)'; this.style.boxShadow='none';">
                    @error('name') <span class="text-[11px] text-red-600 mt-[3px] block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-[var(--bw-text-2)] mb-[5px]">
                        {{ __('Email') }} <span class="text-red-600">*</span>
                    </label>
                    @if ($authUser)
                    <input type="email" wire:model="email" readonly
                        class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none bg-[var(--bw-bg-muted)] cursor-not-allowed box-border">
                    <p class="text-[11px] text-[var(--bw-text-4)] mt-[3px]">{{ __('Locked to your signed-in account.') }}</p>
                    @else
                    <input type="email" wire:model="email" placeholder="{{ __('jane@example.com') }}"
                        class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none transition-[border] duration-[150ms] box-border"
                        onfocus="this.style.borderColor='{{ $brand }}'; this.style.boxShadow='0 0 0 3px {{ $brandLight }}';"
                        onblur="this.style.borderColor='var(--bw-border)'; this.style.boxShadow='none';">
                    @error('email') <span class="text-[11px] text-red-600 mt-[3px] block">{{ $message }}</span> @enderror
                    @endif
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-[var(--bw-text-2)] mb-[5px]">
                        {{ __('Phone') }} <span class="text-[11px] font-normal text-[var(--bw-text-4)]">{{ __('(optional)') }}</span>
                    </label>
                    <input type="tel" wire:model="phone" placeholder="{{ __('+1 (555) 000-0000') }}"
                        class="w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] text-[14px] font-[inherit] text-[var(--bw-text)] outline-none transition-[border] duration-[150ms] box-border"
                        onfocus="this.style.borderColor='{{ $brand }}'; this.style.boxShadow='0 0 0 3px {{ $brandLight }}';"
                        onblur="this.style.borderColor='var(--bw-border)'; this.style.boxShadow='none';">
                </div>
                @endif
            </div>

            @if($showCancellationPolicy && $cancellationPolicyText)
            <div class="mt-4 px-[14px] py-3 rounded-[9px] border border-[var(--bw-border)] text-[12px] text-[var(--bw-text-3)] leading-[1.6] bg-[var(--bw-bg-muted)]">
                <div class="text-[11px] font-semibold text-[var(--bw-text-2)] uppercase tracking-[.5px] mb-[5px]">{{ __('Cancellation policy') }}</div>
                {{ $cancellationPolicyText }}
            </div>
            @else
            <div class="mt-[14px] text-[11px] text-[var(--bw-text-6)] leading-[1.6]">
                {{ __('By continuing you agree to the booking terms and privacy notice.') }}
            </div>
            @endif

            @if($tenantAddress)
            <div class="mt-[10px] flex items-center gap-[5px] text-[11px] text-[var(--bw-text-6)]">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $tenantAddress }}
            </div>
            @endif
        </div>
        @endif

        {{-- ── Step 5: Review ───────────────────────────────────── --}}
        @if ($step === 5)
        @php
            $reviewProviderName = $providerId
                ? \App\Models\Provider::withoutGlobalScope('tenant')
                    ->where('tenant_id', $tenantId)->with('user')->find($providerId)?->user?->name
                : null;
            $reviewWhen = $selectedDate && $selectedStart
                ? \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, MMMM D, YYYY') . ' at ' . $selectedStart
                : null;
            if ($allowMultipleServices && count($activeServiceIds) > 1) {
                $reviewSelectedServices = $services->whereIn('id', $activeServiceIds);
                $reviewServiceLabel = $reviewSelectedServices->pluck('name')->implode(', ')
                    . ' · ' . $reviewSelectedServices->sum('duration_minutes') . ' min';
            } else {
                $reviewServiceLabel = $selectedService
                    ? $selectedService->name . ' · ' . $selectedService->duration_minutes . ' min'
                    : null;
            }
        @endphp
        <div>
            <p class="text-[14px] text-[var(--bw-text-2)] font-medium mb-[18px]">
                {{ __('Take a final look — everything correct?') }}
            </p>

            <div class="rounded-[12px] overflow-hidden border border-[var(--bw-border)]">
                <div class="px-[18px] bg-[var(--bw-bg-muted)]">
                    @foreach ([
                        [__('Service'),  $reviewServiceLabel ?? null],
                        [__('Provider'), $reviewProviderName ?? __('Any available provider')],
                        [__('When'),     $reviewWhen],
                        ...(empty($customFields) ? [
                            [__('Name'),  $name  ?: null],
                            [__('Email'), $email ?: null],
                            [__('Phone'), $phone ?: null],
                            [__('Note'),  $note  ?: null],
                        ] : []),
                    ] as [$label, $value])
                    @if ($value)
                    <div class="flex items-center justify-between gap-4 border-b border-[var(--bw-border-subtle)] py-[13px]">
                        <div class="text-[13px] text-[var(--bw-text-3)] shrink-0">{{ $label }}</div>
                        <div class="text-[13px] font-medium text-[var(--bw-text)] text-right">{{ $value }}</div>
                    </div>
                    @endif
                    @endforeach
                </div>

                @php
                    $reviewTotal = ($allowMultipleServices && count($activeServiceIds) > 1)
                        ? (float) $services->whereIn('id', $activeServiceIds)->sum('price')
                        : (float) ($selectedService?->price ?? 0);
                    $reviewTotalFormatted = ($allowMultipleServices && count($activeServiceIds) > 1)
                        ? ($tenant?->currency ?? '') . ' ' . number_format($reviewTotal, 2)
                        : ($selectedService?->price_formatted ?? '');
                @endphp
                @if ($reviewTotal > 0)
                <div class="flex items-center justify-between gap-4 px-[18px] py-[14px] border-t-2 border-[var(--bw-border)] bg-[var(--bw-bg)]">
                    <div class="text-[13px] text-[var(--bw-text-2)] font-medium">{{ __('Total') }}</div>
                    <div class="text-[18px] font-bold text-[var(--bw-text)]">{{ $reviewTotalFormatted }}</div>
                </div>
                @endif
            </div>

            @php
                $hasCustomAnswers = !empty($customFields) && array_filter($customAnswers, fn($v) => $v !== '' && $v !== null && $v !== false);
            @endphp
            @if ($hasCustomAnswers)
            <div class="mt-[14px] rounded-[12px] overflow-hidden border border-[var(--bw-border)]">
                <div class="px-[18px] bg-[var(--bw-bg-muted)]">
                    @foreach ($customFields as $idx => $field)
                    @if ($field['hidden'] ?? false) @continue @endif
                    @php
                        $ft  = $field['type'] ?? 'short_text';
                        $ans = $customAnswers[$idx] ?? '';
                        if ($ans === '' || $ans === null || $ans === false) continue;
                        if ($ft === 'signature' && !str_starts_with((string)$ans, 'data:image')) continue;
                        $displayAns = $ans;
                        if ($ft === 'file_upload' && $ans instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                            $displayAns = $ans->getClientOriginalName();
                        }
                    @endphp
                    <div class="border-b border-[var(--bw-border-subtle)] py-3">
                        <div class="text-[11px] text-[var(--bw-text-3)] mb-1 uppercase tracking-[.4px] font-medium">
                            {{ $field['label'] }}
                        </div>
                        @if ($ft === 'signature')
                            <div class="rounded-[6px] overflow-hidden border border-[var(--bw-border)] bg-[var(--bw-bg)] max-w-[240px]">
                                <img src="{{ $ans }}" alt="Signature" class="w-full block max-h-[80px] object-contain">
                            </div>
                        @elseif ($ft === 'checkbox')
                            <div class="text-[13px] text-[var(--bw-text)] font-medium">
                                {{ $ans ? '✓ '.__('Yes') : '✗ '.__('No') }}
                            </div>
                        @else
                            <div class="text-[13px] text-[var(--bw-text)] font-medium">{{ $displayAns }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="mt-[14px] px-[14px] py-3 rounded-[10px] flex items-start gap-[10px]"
                 style="background:{{ $brandLight }}; border:1px solid {{ $brandBorder }};">
                <svg class="shrink-0 mt-[1px]" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $brand }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <div class="text-[12px] text-[var(--bw-text-2)] leading-[1.6]">
                    {{ __("You'll get an email confirmation with a calendar invite. Cancel or reschedule any time up to 24h before.") }}
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Step footer — Back · Continue (steps 2,3,4) · Confirm (step 5) ── --}}
    @if ($step >= 2 && $step <= 5)
    <div class="flex items-center gap-[10px] px-7 py-4 bg-[var(--bw-bg-muted)] border-t border-[var(--bw-border)]">
        <button wire:click="goBack"
            class="inline-flex items-center gap-[6px] px-4 py-[9px] text-[13px] font-medium font-[inherit] rounded-lg border border-[var(--bw-border)] bg-transparent text-[var(--bw-text-2)] cursor-pointer transition-[background] duration-[120ms] hover:bg-[var(--bw-hover-bg)]">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            {{ __('Back') }}
        </button>

        <div class="flex-1"></div>

        @if ($step === 2)
        <button wire:click="continueFromProvider"
            class="inline-flex items-center gap-2 px-[22px] py-[9px] text-[13px] font-semibold font-[inherit] border-none text-white cursor-pointer transition-opacity duration-[150ms]"
            style="border-radius:{{ $btnRadius }}; background:{{ $brand }};">
            {{ __('Continue') }}
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
        @endif

        @if ($step === 3)
        <button wire:click="continueFromSlot" @if(! $selectedStart) disabled @endif
            class="inline-flex items-center gap-2 px-[22px] py-[9px] text-[13px] font-semibold font-[inherit] border-none text-white cursor-pointer transition-opacity duration-[150ms]"
            style="border-radius:{{ $btnRadius }};
                   background:{{ $selectedStart ? $brand : 'var(--bw-border)' }};
                   opacity:{{ $selectedStart ? '1' : '0.6' }};">
            {{ __('Continue') }}
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
        @endif

        @if ($step === 4)
        <button wire:click="continueToConfirm"
                wire:loading.attr="disabled" wire:target="continueToConfirm"
                wire:loading.class="opacity-60"
            class="inline-flex items-center gap-2 px-[22px] py-[9px] text-[13px] font-semibold font-[inherit] border-none text-white cursor-pointer transition-opacity duration-[150ms]"
            style="border-radius:{{ $btnRadius }}; background:{{ $brand }};">
            <span wire:loading.remove wire:target="continueToConfirm" class="inline-flex items-center gap-[6px]">
                {{ __('Continue') }}
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </span>
            <span wire:loading wire:target="continueToConfirm">{{ __('Checking…') }}</span>
        </button>
        @endif

        @if ($step === 5)
        <button wire:click="confirm" wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 px-[26px] py-[10px] text-[14px] font-semibold font-[inherit] border-none text-white cursor-pointer transition-opacity duration-[150ms]"
            style="border-radius:{{ $btnRadius }}; background:{{ $brand }};"
            wire:loading.class="opacity-60">
            <span wire:loading.remove wire:target="confirm" class="inline-flex items-center gap-[7px]">
                {{ __('Confirm booking') }}
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span wire:loading wire:target="confirm">{{ __('Confirming…') }}</span>
        </button>
        @endif
    </div>
    @endif

    {{-- ── Step 6: Success ───────────────────────────────────── --}}
    @if ($step === 6)
    @php
        $isDemo = $isDemoBooking;
        $successProviderName = null;
        if ($providerId) {
            $successProviderName = \App\Models\Provider::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)->with('user')->find($providerId)?->user?->name;
        }
        if ($allowMultipleServices && count($activeServiceIds) > 1) {
            $successServiceName = $services->whereIn('id', $activeServiceIds)->pluck('name')->implode(', ');
        } else {
            $successServiceName = $selectedService?->name;
        }
    @endphp
    @if (is_null($bookingId))
    <div class="px-7 py-[60px] text-center">
        <div class="text-[14px] text-[var(--bw-text-3)] mb-4">{{ __('No active booking session.') }}</div>
        <a href="{{ url()->current() }}" class="text-[13px] font-semibold no-underline" style="color:{{ $brand }};">
            ← {{ __('Start a booking') }}
        </a>
    </div>
    @else
    <div class="px-7 pt-[44px] pb-9 text-center">
        <div class="w-[60px] h-[60px] rounded-[30px] mx-auto mb-[18px] text-white flex items-center justify-center"
             style="background:{{ $brand }}; box-shadow: 0 0 0 10px {{ $brandLight }};">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="text-[24px] font-bold tracking-[-0.4px] text-[var(--bw-text)]">{{ $isDemo ? __("You're booked!") : $successTitle }}</div>
        <div class="text-[13px] text-[var(--bw-text-3)] mt-2 leading-[1.6] max-w-[340px] mx-auto">
            @if ($isDemo)
                {{ __('This is a demo — bookings are not saved. In production your confirmation email would be sent here.') }}
            @else
                {{ $successBody }}
            @endif
        </div>
        @if ($isDemo)
        <div class="mt-4 mx-auto max-w-[380px] px-[14px] py-[10px] bg-[#fffbeb] border border-[#fde68a] rounded-lg text-[12px] text-[#92400e] flex items-center gap-2 text-left">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ __('Demo mode — changes are not persisted') }}
        </div>
        @endif
        <div class="mt-5 mx-auto max-w-[380px] text-left rounded-[12px] overflow-hidden border border-[var(--bw-border)]">
            <div class="px-[18px] py-4 bg-[var(--bw-bg-muted)]">
                @foreach ([
                    [__('Service'),   $successServiceName ?? null],
                    [__('Provider'),  $successProviderName],
                    [__('Date'),      $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('D, d M Y') : null],
                    [__('Time'),      $selectedStart],
                    [__('Booking #'), $isDemo ? 'DEMO' : ($bookingId ? '#'.$bookingId : null)],
                ] as [$label, $value])
                @if ($value)
                <div class="flex items-baseline justify-between gap-3 border-b border-[var(--bw-border)] py-[6px]">
                    <div class="text-[12px] text-[var(--bw-text-3)]">{{ $label }}</div>
                    <div class="text-[13px] font-medium text-[var(--bw-text)] text-right">{{ $value }}</div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @if (! $isDemo && $bookingToken)
        @php
            $gcalEnd = $selectedEnd ?? $selectedStart;
            $gcalStartFmt = str_replace('-', '', $selectedDate) . 'T' . str_replace(':', '', $selectedStart) . '00';
            $gcalEndFmt   = str_replace('-', '', $selectedDate) . 'T' . str_replace(':', '', $gcalEnd) . '00';
            $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
                . '&text=' . rawurlencode(($selectedService?->name ?? 'Booking') . ' — Booking')
                . '&dates=' . $gcalStartFmt . '/' . $gcalEndFmt
                . '&ctz=' . rawurlencode($tenantTimezone ?? 'UTC')
                . '&details=' . rawurlencode('Booking ID: ' . ($bookingId ?? ''));
            $icalUrl = route('booking.ical', $bookingToken);
        @endphp
        <div class="grid grid-cols-3 gap-[6px] max-w-[380px] mx-auto mt-4">
            @foreach ([['Google', $gcalUrl], ['Apple', $icalUrl], ['Outlook', $icalUrl]] as [$cal, $href])
            <a href="{{ $href }}"
               class="px-[6px] py-[9px] text-[12px] font-medium font-[inherit] rounded-lg border border-[var(--bw-border)] bg-[var(--bw-bg)] text-[var(--bw-text)] no-underline text-center flex items-center justify-center gap-[5px] transition-all duration-[120ms] hover:bg-[var(--bw-bg-muted)]">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                {{ $cal }}
            </a>
            @endforeach
        </div>
        @endif
        <div class="mt-6 max-w-[380px] mx-auto">
            <button wire:click="$set('step', 1)"
                class="w-full py-[11px] text-[13px] font-medium font-[inherit] rounded-lg border border-[var(--bw-border)] bg-transparent text-[var(--bw-text-2)] cursor-pointer transition-all duration-[120ms] hover:bg-[var(--bw-bg-muted)]">
                {{ __('Book another') }}
            </button>
        </div>
    </div>
    @endif
    @endif {{-- end step 6 --}}

</div>
