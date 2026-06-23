{{-- Lumina theme: two-column with branded gradient sidebar --}}
@php
    $btnRadius = match($buttonStyle ?? 'rounded') {
        'pill'  => '999px',
        'sharp' => '4px',
        default => '10px',
    };

    $sidebarSteps = $stepLabels; // ['Service','Provider','Date & Time','Your Details','Confirm']

    // Summary items for sidebar
    $summaryItems = [];
    if ($step > 1 && $selectedService) {
        if ($allowMultipleServices && count($activeServiceIds) > 1) {
            $selSvcs = $services->whereIn('id', $activeServiceIds);
            $summaryItems[] = ['label' => __('Services'), 'value' => __(':count selected · :min min', ['count' => $selSvcs->count(), 'min' => $selSvcs->sum('duration_minutes')])];
        } else {
            $summaryItems[] = ['label' => __('Service'), 'value' => $selectedService->name . ' · ' . __(':min min', ['min' => $selectedService->duration_minutes])];
        }
    }
    if ($step > 2 && $providerId) {
        $prov = $this->getProviders()->firstWhere('id', $providerId);
        if ($prov) $summaryItems[] = ['label' => __('Provider'), 'value' => $prov->user->name];
    }
    if ($step > 3 && $selectedDate) {
        $summaryItems[] = ['label' => __('Date'), 'value' => \Carbon\Carbon::parse($selectedDate)->format('D, d M Y')];
        if ($selectedStart) $summaryItems[] = ['label' => __('Time'), 'value' => $selectedStart];
    }

    $stepHeadings = [
        1 => [__('Choose a service'), __('What would you like to book?')],
        2 => [__('Choose a provider'), __('Who would you like to see?')],
        3 => [__('Pick a date & time'), __('Choose when works for you')],
        4 => [__('Your details'), __('Just a few details to confirm your spot')],
        5 => [__('Review & confirm'), __('Everything look right?')],
        6 => [__('Booking confirmed'), ''],
    ];
    $headingMain = $stepHeadings[$step][0] ?? '';
    $headingSub  = $stepHeadings[$step][1] ?? '';

    // Monogram for sidebar
    $sidebarInitials = collect(explode(' ', $tenant?->name ?? 'S'))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
@endphp

{{-- ── Lumina responsive ─────────────────────────────────────────────── --}}
<style>
@media (max-width: 599px) {
    .lum-sidebar { display: none !important; }
    .lum-content-head { padding: 18px 20px 14px !important; }
    .lum-content-body { padding: 16px 20px 20px !important; overflow-y: visible !important; }
    .lum-content-foot { padding: 12px 16px !important; }
    .lum-mobile-progress { display: flex !important; }
}
</style>

<div class="booking-card bw-card-shadow-lg flex overflow-hidden rounded-2xl border border-[var(--bw-border)] bg-[var(--bw-bg)] min-h-[560px]"
     data-force-dark="{{ ($forceDarkMode ?? false) ? '1' : '0' }}"
     data-match-system="{{ ($matchSystemTheme ?? true) ? '1' : '0' }}">

    {{-- ── LEFT: Gradient sidebar ──────────────────────────────── --}}
    @if ($step < 6)
    <div class="lum-sidebar relative flex flex-shrink-0 flex-col overflow-hidden"
         style="background: linear-gradient(160deg, {{ $brand }} 0%, color-mix(in srgb, {{ $brand }} 70%, #000) 100%);">

        {{-- Decorative dots pattern --}}
        <div class="lum-dot-pattern pointer-events-none absolute inset-0 opacity-[.12]"></div>

        {{-- Decorative circles --}}
        <div class="lum-deco-circle pointer-events-none absolute -bottom-[60px] -right-[60px] h-[200px] w-[200px] rounded-full"></div>
        <div class="lum-deco-circle pointer-events-none absolute -bottom-5 -right-5 h-[120px] w-[120px] rounded-full"></div>

        <div class="relative z-[1] flex h-full flex-col px-[22px] py-7">

            {{-- Business identity --}}
            <div class="mb-8 flex items-center gap-[10px]">
                @if ($tenant?->logo)
                    <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}"
                         class="lum-logo-img-bg h-8 w-auto max-w-[80px] flex-shrink-0 rounded-md object-contain">
                @else
                    <div class="lum-monogram-bg flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-[10px] text-[13px] font-bold"
                         style="color:{{ $brand }};">
                        {{ $sidebarInitials }}
                    </div>
                @endif
                <div>
                    <div class="text-[13px] font-bold leading-[1.2] text-white">{{ $tenant?->name ?? config('app.name') }}</div>
                    @if($tenantTagline)
                    <div class="lum-text-faint mt-0.5 text-[10px] leading-[1.3]">{{ Str::limit($tenantTagline, 40) }}</div>
                    @endif
                </div>
            </div>

            {{-- Step list --}}
            <div class="flex flex-1 flex-col gap-1">
                @foreach ($sidebarSteps as $i => $label)
                @php
                    $n       = $i + 1;
                    $isDone  = $currentStep > $n;
                    $isNow   = $currentStep === $n;
                @endphp
                <div class="flex items-center gap-[10px] rounded-lg px-[10px] py-[7px] transition-[background] duration-150"
                     style="background:{{ $isNow ? 'rgba(255,255,255,0.18)' : 'transparent' }};">
                    {{-- Step indicator --}}
                    <div class="flex h-[22px] w-[22px] flex-shrink-0 items-center justify-center rounded-full"
                         style="background:{{ $isDone ? 'rgba(255,255,255,0.95)' : ($isNow ? 'rgba(255,255,255,0.25)' : 'rgba(255,255,255,0.12)') }};
                                border:{{ $isNow ? '2px solid rgba(255,255,255,0.8)' : '1.5px solid rgba(255,255,255,0.25)' }};">
                        @if ($isDone)
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="{{ $brand }}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <span class="text-[10px] font-bold" style="color:{{ $isNow ? '#fff' : 'rgba(255,255,255,0.5)' }};">{{ $n }}</span>
                        @endif
                    </div>
                    <span class="text-[12px]"
                          style="font-weight:{{ $isNow ? '600' : '400' }}; color:{{ $isNow ? '#fff' : ($isDone ? 'rgba(255,255,255,0.8)' : 'rgba(255,255,255,0.45)') }};">
                        {{ $label }}
                    </span>
                </div>
                @endforeach
            </div>

            {{-- Summary card (shows after step 1) --}}
            @if (!empty($summaryItems))
            <div class="lum-summary-bg mt-5 rounded-[10px] border border-[rgba(255,255,255,0.18)] px-[14px] py-3">
                <div class="lum-text-dim mb-2 text-[10px] font-bold uppercase tracking-[.6px]">{{ __('Your selection') }}</div>
                @foreach ($summaryItems as $item)
                <div class="mb-[7px] flex flex-col gap-px">
                    <div class="lum-text-dimmer text-[9.5px] uppercase tracking-[.4px]">{{ $item['label'] }}</div>
                    <div class="text-[12px] font-semibold text-white">{{ $item['value'] }}</div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Contact info --}}
            @if($tenantPhone || $tenantWebsite)
            <div class="mt-4 flex flex-col gap-[5px]">
                @if($tenantPhone)
                <a href="tel:{{ $tenantPhone }}"
                   class="lum-text-dim inline-flex items-center gap-[5px] text-[11px] no-underline">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.57 3.41 2 2 0 0 1 3.54 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.59a16 16 0 0 0 6 6l.9-.9a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    {{ $tenantPhone }}
                </a>
                @endif
                @if($tenantWebsite)
                <a href="{{ $tenantWebsite }}" target="_blank" rel="noopener"
                   class="lum-text-dim inline-flex items-center gap-[5px] text-[11px] no-underline">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    {{ parse_url($tenantWebsite, PHP_URL_HOST) ?: $tenantWebsite }}
                </a>
                @endif
            </div>
            @endif

            {{-- Language picker --}}
            @php
                $langMeta = ['en'=>['flag'=>'🇬🇧','label'=>'EN'],'ro'=>['flag'=>'🇷🇴','label'=>'RO'],'es'=>['flag'=>'🇪🇸','label'=>'ES'],'de'=>['flag'=>'🇩🇪','label'=>'DE'],'fr'=>['flag'=>'🇫🇷','label'=>'FR'],'ar'=>['flag'=>'🇸🇦','label'=>'AR'],'ru'=>['flag'=>'🇷🇺','label'=>'RU'],'zh'=>['flag'=>'🇨🇳','label'=>'ZH'],'hi'=>['flag'=>'🇮🇳','label'=>'HI']];
                $enabledLocales = \App\Http\Middleware\SetLocale::enabledLocales();
                $currentLocale  = app()->getLocale();
            @endphp
            @if(count($enabledLocales) > 1)
            <div class="mt-4" style="position:relative;" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open"
                        class="lum-text-dim inline-flex items-center gap-[5px] text-[11px]"
                        style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:6px;padding:3px 8px 3px 6px;cursor:pointer;font-family:inherit;">
                    <span style="font-size:13px;line-height:1;">{{ $langMeta[$currentLocale]['flag'] ?? '🌐' }}</span>
                    <span>{{ $langMeta[$currentLocale]['label'] ?? strtoupper($currentLocale) }}</span>
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :style="open ? 'transform:rotate(180deg)' : ''" style="transition:transform .15s;"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     style="position:absolute;bottom:calc(100% + 6px);left:0;z-index:50;min-width:120px;background:#1e1b4b;border:1px solid rgba(255,255,255,0.15);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.3);overflow:hidden;transform-origin:bottom left;">
                    @foreach($enabledLocales as $loc)
                    @php $meta = $langMeta[$loc] ?? ['flag'=>'🌐','label'=>strtoupper($loc)]; @endphp
                    <form method="POST" action="{{ route('locale.switch') }}" style="margin:0;">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $loc }}">
                        <button type="submit"
                                style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 12px;border:none;background:{{ $loc === $currentLocale ? 'rgba(255,255,255,0.12)' : 'transparent' }};cursor:pointer;font-family:inherit;text-align:left;transition:background .1s;color:{{ $loc === $currentLocale ? '#fff' : 'rgba(255,255,255,0.65)' }};"
                                onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                                onmouseout="this.style.background='{{ $loc === $currentLocale ? 'rgba(255,255,255,0.12)' : 'transparent' }}'">
                            <span style="font-size:14px;line-height:1;">{{ $meta['flag'] }}</span>
                            <span style="font-size:12px;font-weight:{{ $loc === $currentLocale ? '700' : '500' }};">{{ $meta['label'] }}</span>
                            @if($loc === $currentLocale)
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:auto;"><polyline points="20 6 9 17 4 12"/></svg>
                            @endif
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- ── RIGHT: Content panel ─────────────────────────────────── --}}
    <div class="flex min-w-0 flex-1 flex-col">

        @if ($step < 6)
        {{-- Step label + heading --}}
        <div class="lum-content-head border-b border-[var(--bw-border)] px-8 pb-5 pt-7">
            <div class="mb-[6px] text-[10.5px] font-bold uppercase tracking-[.8px]" style="color:{{ $brand }};">
                Step {{ $currentStep }} of {{ count($stepLabels) }}
            </div>
            <div class="text-[20px] font-bold tracking-[-0.2px] text-[var(--bw-text)]">{{ $headingMain }}</div>
            @if($headingSub)
            <div class="mt-[3px] text-[13px] text-[var(--bw-text-3)]">{{ $headingSub }}</div>
            @endif
        </div>

        {{-- Mobile step-progress strip (hidden on desktop via inline display:none, shown by media query) --}}
        <div class="lum-mobile-progress hidden gap-[5px] px-5 pt-[10px]">
            @foreach ($stepLabels as $i => $label)
            @php $n = $i + 1; $isActive = $currentStep === $n; $isDone = $currentStep > $n; @endphp
            <div class="h-[3px] flex-1 rounded-[2px]"
                 style="background:{{ ($isDone || $isActive) ? $brand : 'var(--bw-border)' }}; opacity:{{ ($isDone || $isActive) ? '1' : '0.5' }};"></div>
            @endforeach
        </div>
        @endif

        {{-- Step content --}}
        <div class="lum-content-body flex-1 overflow-y-auto {{ $step < 6 ? 'px-8 pb-7 pt-6' : '' }}">

            {{-- ── Step 1: Service ───────────────────────────────── --}}
            @if ($step === 1)
            <div>
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
                        class="flex w-full cursor-pointer items-start gap-[14px] rounded-xl px-[18px] py-4 text-left font-[inherit] transition-all duration-[120ms]"
                        style="border:1.5px solid {{ $sel ? $brand : 'var(--bw-border)' }}; background:{{ $sel ? $brandLight : 'var(--bw-bg)' }};">

                        @if ($allowMultipleServices)
                        <div class="mt-0.5 flex h-[18px] w-[18px] flex-shrink-0 items-center justify-center rounded-[5px]"
                             style="border:2px solid {{ $sel ? $brand : 'var(--bw-border)' }}; background:{{ $sel ? $brand : 'transparent' }};">
                            @if ($sel)
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            @endif
                        </div>
                        @else
                        <div class="mt-0.5 flex h-[18px] w-[18px] flex-shrink-0 items-center justify-center rounded-[9px]"
                             style="border:2px solid {{ $sel ? $brand : 'var(--bw-border)' }}; background:{{ $sel ? $brand : 'transparent' }};">
                            @if ($sel)
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            @endif
                        </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline justify-between gap-3">
                                <div class="text-[14px] font-semibold text-[var(--bw-text)]">{{ $service->name }}</div>
                                <div class="flex-shrink-0 text-[13px] font-semibold text-[var(--bw-text-2)]">{{ $service->price_formatted }}</div>
                            </div>
                            @if ($service->description)
                            <div class="mt-1 text-[12px] text-[var(--bw-text-3)]">{{ $service->description }}</div>
                            @endif
                            <div class="mt-[6px] inline-flex items-center gap-1 text-[11px] text-[var(--bw-text-5)]">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ __(':min min', ['min' => $service->duration_minutes]) }}
                            </div>
                        </div>
                    </button>
                    @empty
                    <p class="py-8 text-center text-[var(--bw-text-3)]">{{ __('No services available yet.') }}</p>
                    @endforelse
                </div>

                @if ($allowMultipleServices && count($activeServiceIds) > 1)
                <div class="mt-3 flex items-center justify-between rounded-lg px-[14px] py-[10px] text-[12px] text-[var(--bw-text)]"
                     style="background:{{ $brandLight }}; border:1px solid {{ $brandBorder }};">
                    <span><strong>{{ trans_choice(':count service selected|:count services selected', count($activeServiceIds), ['count' => count($activeServiceIds)]) }}</strong></span>
                    <span>{{ $totalDuration }} min
                        @if($totalPrice > 0) · {{ $tenant?->currency ?? '' }} {{ number_format($totalPrice, 2) }} @endif
                    </span>
                </div>
                @endif
            </div>
            @endif

            {{-- ── Step 2: Provider ──────────────────────────────── --}}
            @if ($step === 2)
            <div>
                <div class="lum-provider-grid grid gap-[10px]">
                    @php $noSel = $providerId === null; @endphp
                    <button wire:click="selectProvider(0)"
                        class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-4 py-[14px] text-left font-[inherit] transition-all duration-[120ms]"
                        style="border:{{ $noSel ? '2px' : '1.5px' }} solid {{ $noSel ? $brand : 'var(--bw-border)' }}; background:{{ $noSel ? $brandLight : 'var(--bw-bg)' }};">
                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-[22px] border border-dashed border-[var(--bw-border)] bg-[var(--bw-bg-raised)]">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a1a1aa" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[13px] font-semibold text-[var(--bw-text)]">{{ __('No preference') }}</div>
                            <div class="mt-0.5 text-[11px] text-[var(--bw-text-3)]">{{ __('Any available provider') }}</div>
                        </div>
                        @if ($noSel)
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $brand }}" stroke="none"><circle cx="12" cy="12" r="12"/><polyline points="7 12 10 15 17 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                        @endif
                    </button>

                    @forelse ($providers as $provider)
                    @php
                        $sel = $providerId === $provider->id;
                        $colors = ['#fbbf24','#a78bfa','#34d399','#60a5fa','#f87171','#fb923c'];
                        $colorIdx = crc32($provider->user->name ?? 'P') % count($colors);
                        $avatarColor = $colors[abs($colorIdx)];
                        $provInitials = collect(explode(' ', $provider->user->name ?? 'P'))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
                    @endphp
                    <button wire:click="selectProvider({{ $provider->id }})"
                        class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-4 py-[14px] text-left font-[inherit] transition-all duration-[120ms]"
                        style="border:{{ $sel ? '2px' : '1.5px' }} solid {{ $sel ? $brand : 'var(--bw-border)' }}; background:{{ $sel ? $brandLight : 'var(--bw-bg)' }};">
                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-[22px] text-[15px] font-bold text-white"
                             style="background:{{ $avatarColor }};">
                            {{ $provInitials }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[13px] font-semibold text-[var(--bw-text)]">{{ $provider->user->name }}</div>
                            @if ($provider->job_title)
                            <div class="mt-0.5 text-[11px] text-[var(--bw-text-3)]">{{ $provider->job_title }}</div>
                            @endif
                        </div>
                        @if ($sel)
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $brand }}" stroke="none"><circle cx="12" cy="12" r="12"/><polyline points="7 12 10 15 17 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                        @endif
                    </button>
                    @empty
                    <p class="col-span-full py-8 text-center text-[var(--bw-text-3)]">{{ __('No providers available for this service.') }}</p>
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
        @if (($datePickerStyle ?? 'monthly') === 'weekly')
            {{-- ── Weekly strip ──────────────────────────────── --}}
            @include('livewire.booking.partials.date-picker-weekly')

            {{-- Slots below the strip --}}
            <div class="mt-4">
                <div class="mb-[10px] flex items-center gap-[6px] text-[11px] font-bold uppercase tracking-[.5px] text-[var(--bw-text-3)]">
                    @if ($selectedDate)
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ \Carbon\Carbon::parse($selectedDate)->format('D, d M') }}
                    @else
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ __('Available times') }}
                    @endif
                </div>
                @if ($selectedDate)
                    @if ($availableSlots->isEmpty())
                        <div class="rounded-[10px] border border-dashed border-[var(--bw-border)] p-[18px] text-center text-[13px] text-[var(--bw-text-3)]">
                            {{ __('No slots available — try another day.') }}
                        </div>
                    @else
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ($availableSlots as $slot)
                            @php $slotSel = $selectedStart === $slot['start']; @endphp
                            <button wire:click="selectSlot('{{ $slot['start'] }}', '{{ $slot['end'] }}')"
                                class="cursor-pointer rounded-[9px] px-[6px] py-[10px] text-center font-[inherit] text-[13px] transition-all duration-[120ms]"
                                style="font-weight:{{ $slotSel ? '700' : '500' }};
                                       border:{{ $slotSel ? '2px' : '1px' }} solid {{ $slotSel ? $brand : 'var(--bw-border)' }};
                                       background:{{ $slotSel ? $brand : 'var(--bw-bg)' }};
                                       color:{{ $slotSel ? '#fff' : 'var(--bw-text)' }};">
                                {{ $slot['start'] }}
                            </button>
                            @endforeach
                        </div>
                        <div class="mt-[10px] flex items-center gap-[5px] text-[11px] text-[var(--bw-text-4)]">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            All times in {{ $tenant?->timezone ?? 'UTC' }}
                        </div>
                    @endif
                @else
                    <div class="rounded-[10px] border border-dashed border-[var(--bw-border)] p-[18px] text-center text-[13px] text-[var(--bw-text-4)]">
                        {{ __('Select a date above to see available times') }}
                    </div>
                @endif
            </div>
        @else
            {{-- Vertical stack: full-width calendar → slots below --}}
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
            }" class="flex flex-col gap-4">

                {{-- Full-width calendar --}}
                <div class="overflow-hidden rounded-xl border border-[var(--bw-border)] select-none">

                    {{-- Month header --}}
                    <div class="flex items-center justify-between border-b border-[var(--bw-border-muted)] bg-[var(--bw-bg-subtle)] px-4 py-3 text-[var(--bw-text)]">
                        <button type="button" @click="prevMonth()"
                            :style="canPrev() ? 'opacity:1;cursor:pointer;' : 'opacity:0.25;cursor:default;'"
                            class="flex h-[30px] w-[30px] items-center justify-center rounded-[7px] border border-[var(--bw-border)] bg-[var(--bw-bg)] p-0">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <span x-text="monthLabel" class="text-[14px] font-semibold text-[var(--bw-text)]"></span>
                        <button type="button" @click="nextMonth()"
                            :style="canNext() ? 'opacity:1;cursor:pointer;' : 'opacity:0.25;cursor:default;'"
                            class="flex h-[30px] w-[30px] items-center justify-center rounded-[7px] border border-[var(--bw-border)] bg-[var(--bw-bg)] p-0">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>

                    {{-- DOW headers --}}
                    <div class="grid grid-cols-7 bg-[var(--bw-bg-subtle)] px-3 pb-1 pt-2">
                        <template x-for="d in dow" :key="d">
                            <div x-text="d" class="text-center text-[11px] font-semibold tracking-[.2px] text-[var(--bw-text-5)]"></div>
                        </template>
                    </div>

                    {{-- Day grid --}}
                    <div class="grid grid-cols-7 gap-[3px] px-3 pb-[14px] pt-[6px]">
                        <template x-for="(date,i) in pad(cells)" :key="i">
                            <div class="flex items-center justify-center">
                                <button
                                    type="button"
                                    x-text="date ? new Date(date+'T00:00').getDate() : ''"
                                    @click="selectDay(date)"
                                    :disabled="isDisabled(date)"
                                    :style="`
                                        width:36px; height:36px; border-radius:50%;
                                        font-size:13px; font-family:inherit;
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

                {{-- Time slots below calendar --}}
                <div>
                    <div class="mb-[10px] flex items-center gap-[6px] text-[11px] font-bold uppercase tracking-[.5px] text-[var(--bw-text-3)]">
                        @if ($selectedDate)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ \Carbon\Carbon::parse($selectedDate)->format('D, d M') }}
                        @else
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ __('Available times') }}
                        @endif
                    </div>

                    @if ($selectedDate)
                        @if ($availableSlots->isEmpty())
                            <div class="rounded-[10px] border border-dashed border-[var(--bw-border)] p-[18px] text-center text-[13px] text-[var(--bw-text-3)]">
                                {{ __('No slots available — try another day.') }}
                            </div>
                        @else
                            <div class="grid grid-cols-4 gap-2">
                                @foreach ($availableSlots as $slot)
                                @php $slotSel = $selectedStart === $slot['start']; @endphp
                                <button wire:click="selectSlot('{{ $slot['start'] }}', '{{ $slot['end'] }}')"
                                    class="cursor-pointer rounded-[9px] px-[6px] py-[10px] text-center font-[inherit] text-[13px] transition-all duration-[120ms]"
                                    style="font-weight:{{ $slotSel ? '700' : '500' }};
                                           border:{{ $slotSel ? '2px' : '1px' }} solid {{ $slotSel ? $brand : 'var(--bw-border)' }};
                                           background:{{ $slotSel ? $brand : 'var(--bw-bg)' }};
                                           color:{{ $slotSel ? '#fff' : 'var(--bw-text)' }};">
                                    {{ $slot['start'] }}
                                </button>
                                @endforeach
                            </div>
                            <div class="mt-[10px] flex items-center gap-[5px] text-[11px] text-[var(--bw-text-4)]">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                All times in {{ $tenant?->timezone ?? 'UTC' }}
                            </div>
                        @endif
                    @else
                        <div class="rounded-[10px] border border-dashed border-[var(--bw-border)] p-[18px] text-center text-[13px] text-[var(--bw-text-4)]">
                            {{ __('Select a date above to see available times') }}
                        </div>
                    @endif
                </div>

            </div>
        @endif {{-- end monthly/weekly --}}
            @endif

            {{-- ── Step 4: Your Details ──────────────────────────── --}}
            @if ($step === 4)
            @php $authUser = auth()->user(); @endphp
            <div>
                @if ($authUser)
                <div class="mb-4 flex items-center gap-[10px] rounded-[9px] border border-[#e0d9ff] bg-[#f5f3ff] px-[14px] py-[10px]">
                    <svg class="flex-shrink-0 text-[#7c3aed]" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div>
                        <div class="text-[12px] font-semibold leading-[1.3] text-[#5b21b6]">{{ $authUser->name }}</div>
                        <div class="text-[11px] text-[#7c3aed]">{{ $authUser->email }}</div>
                    </div>
                </div>
                @endif
                <div class="flex flex-col gap-[14px]">
                    @if (!empty($customFields))
                        @foreach ($customFields as $idx => $field)
                        @if ($field['hidden'] ?? false) @continue @endif
                        <div>
                            <label class="mb-[5px] block text-[13px] font-medium text-[var(--bw-text-2)]">
                                {{ $field['label'] }}
                                @if ($field['required'] ?? false) <span class="text-[#dc2626]">*</span> @endif
                            </label>
                            @php $ft = $field['type'] ?? 'short_text'; @endphp
                            @if (in_array($ft, ['textarea']))
                                <textarea wire:model="customAnswers.{{ $idx }}" rows="3"
                                    class="box-border w-full resize-y rounded-lg border border-[var(--bw-border)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none"
                                    onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'"></textarea>
                            @elseif (in_array($ft, ['select', 'dropdown']))
                                <select wire:model="customAnswers.{{ $idx }}"
                                    class="box-border w-full rounded-lg border border-[var(--bw-border)] bg-[var(--bw-bg)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none">
                                    <option value="">{{ $field['placeholder'] ?? '— Select —' }}</option>
                                    @foreach ($field['options'] ?? [] as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @elseif ($ft === 'radio_group')
                                <div class="mt-1 flex flex-col gap-2">
                                    @foreach ($field['options'] ?? [] as $opt)
                                    <label class="flex cursor-pointer items-center gap-[9px]">
                                        <input type="radio" wire:model="customAnswers.{{ $idx }}" value="{{ $opt }}"
                                            class="h-4 w-4 cursor-pointer"
                                            style="accent-color:{{ $brand }};">
                                        <span class="text-[14px] text-[var(--bw-text)]">{{ $opt }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            @elseif ($ft === 'checkbox')
                                <label class="flex cursor-pointer items-center gap-[9px]">
                                    <input type="checkbox" wire:model="customAnswers.{{ $idx }}"
                                        class="h-4 w-4 cursor-pointer"
                                        style="accent-color:{{ $brand }};">
                                    <span class="text-[14px] text-[var(--bw-text)]">{{ $field['label'] }}</span>
                                </label>
                            @elseif ($ft === 'date_picker')
                                <input type="date" wire:model="customAnswers.{{ $idx }}"
                                    class="box-border w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none"
                                    onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">
                            @elseif ($ft === 'time_slot')
                                <input type="time" wire:model="customAnswers.{{ $idx }}"
                                    class="box-border w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none"
                                    onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">
                            @elseif ($ft === 'file_upload')
                                <input type="file" wire:model="customAnswers.{{ $idx }}"
                                    class="box-border w-full rounded-lg border border-[var(--bw-border)] bg-[var(--bw-bg-raised)] px-3 py-2 font-[inherit] text-[13px] text-[var(--bw-text)] outline-none">
                            @elseif ($ft === 'email')
                                @php $isAuthEmail = $authUser && strtolower($customAnswers[$idx] ?? '') === strtolower($authUser->email ?? ''); @endphp
                                <input type="email" wire:model="customAnswers.{{ $idx }}"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    @if($isAuthEmail) readonly @endif
                                    class="box-border w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none {{ $isAuthEmail ? 'cursor-not-allowed bg-[var(--bw-bg-muted)]' : '' }}"
                                    @if(!$isAuthEmail) onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'" @endif>
                                @if($isAuthEmail)
                                <p class="mt-[3px] text-[11px] text-[var(--bw-text-4)]">{{ __("Locked to your signed-in account.") }}</p>
                                @endif
                            @elseif ($ft === 'phone')
                                <input type="tel" wire:model="customAnswers.{{ $idx }}"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    class="box-border w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none"
                                    onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">
                            @elseif ($ft === 'signature')
                                @php $sigVal = $customAnswers[$idx] ?? ''; @endphp
                                <div wire:ignore
                                     x-data="{
                                        drawing: false, hasStroke: false,
                                        init() {
                                            const canvas = this.$refs.canvas;
                                            const ctx = canvas.getContext('2d');
                                            ctx.lineWidth = 2.5; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#333';
                                            const saved = canvas.dataset.saved;
                                            if (saved && saved.startsWith('data:image')) {
                                                this.hasStroke = true;
                                                const img = new Image();
                                                img.onload = () => ctx.drawImage(img, 0, 0);
                                                img.src = saved;
                                            }
                                            const pos = (e) => { const r = canvas.getBoundingClientRect(); const s = e.touches ? e.touches[0] : e; return { x: (s.clientX - r.left) * (canvas.width / r.width), y: (s.clientY - r.top) * (canvas.height / r.height) }; };
                                            const start = (e) => { e.preventDefault(); this.drawing = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); };
                                            const move = (e) => { e.preventDefault(); if (!this.drawing) return; const p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); this.hasStroke = true; };
                                            const end = () => { if (!this.drawing) return; this.drawing = false; const url = canvas.toDataURL(); canvas.dataset.saved = url; this.$wire.setCustomAnswer({{ $idx }}, url); };
                                            canvas.addEventListener('mousedown', start); canvas.addEventListener('mousemove', move); canvas.addEventListener('mouseup', end);
                                            canvas.addEventListener('touchstart', start, { passive: false }); canvas.addEventListener('touchmove', move, { passive: false }); canvas.addEventListener('touchend', end);
                                        },
                                        clear() { const canvas = this.$refs.canvas; canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height); canvas.dataset.saved = ''; this.hasStroke = false; this.$wire.setCustomAnswer({{ $idx }}, ''); }
                                    }">
                                    <div class="relative overflow-hidden rounded-lg border border-[var(--bw-border)] bg-[var(--bw-bg)]">
                                        <canvas x-ref="canvas" width="600" height="160" data-saved="{{ $sigVal }}"
                                            class="block h-[120px] w-full cursor-crosshair touch-none"></canvas>
                                        <div class="pointer-events-none absolute left-[10px] top-[6px] text-[10px] text-[var(--bw-text-5)]" x-show="!hasStroke">{{ __('Sign here') }} ↓</div>
                                        <button type="button" x-show="hasStroke" @click="clear()"
                                            class="absolute right-2 top-[6px] cursor-pointer rounded-[5px] border border-[var(--bw-border)] bg-[var(--bw-bg-muted)] px-2 py-0.5 text-[11px] text-[var(--bw-text-3)]">{{ __("Clear") }}</button>
                                    </div>
                                </div>
                            @else
                                <input type="text" wire:model="customAnswers.{{ $idx }}"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    class="box-border w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none"
                                    onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">
                            @endif
                            @error("customAnswers.$idx") <span class="mt-[3px] block text-[11px] text-[#dc2626]">{{ $message }}</span> @enderror
                        </div>
                        @endforeach
                    @else
                    <div>
                        <label class="mb-[5px] block text-[13px] font-medium text-[var(--bw-text-2)]">{{ __("Full Name") }} <span class="text-[#dc2626]">*</span></label>
                        <input type="text" wire:model="name" placeholder="{{ __('Jane Doe') }}"
                            class="box-border w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none"
                            onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">
                        @error('name') <span class="mt-[3px] block text-[11px] text-[#dc2626]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-[5px] block text-[13px] font-medium text-[var(--bw-text-2)]">{{ __("Email") }} <span class="text-[#dc2626]">*</span></label>
                        @if ($authUser)
                        <input type="email" wire:model="email" readonly
                            class="box-border w-full cursor-not-allowed rounded-lg border border-[var(--bw-border)] bg-[var(--bw-bg-muted)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none">
                        <p class="mt-[3px] text-[11px] text-[var(--bw-text-4)]">{{ __("Locked to your signed-in account.") }}</p>
                        @else
                        <input type="email" wire:model="email" placeholder="{{ __('jane@example.com') }}"
                            class="box-border w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none"
                            onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">
                        @error('email') <span class="mt-[3px] block text-[11px] text-[#dc2626]">{{ $message }}</span> @enderror
                        @endif
                    </div>
                    <div>
                        <label class="mb-[5px] block text-[13px] font-medium text-[var(--bw-text-2)]">
                            {{ __('Phone') }} <span class="text-[11px] font-normal text-[var(--bw-text-4)]">({{ __('optional') }})</span>
                        </label>
                        <input type="tel" wire:model="phone" placeholder="{{ __('+1 (555) 000-0000') }}"
                            class="box-border w-full rounded-lg border border-[var(--bw-border)] px-3 py-[9px] font-[inherit] text-[14px] text-[var(--bw-text)] outline-none"
                            onfocus="this.style.borderColor='{{ $brand }}'" onblur="this.style.borderColor='var(--bw-border)'">
                    </div>
                    @endif
                </div>

                @if($showCancellationPolicy && $cancellationPolicyText)
                <div class="mt-4 rounded-[9px] border border-[var(--bw-border)] bg-[var(--bw-bg-muted)] px-[14px] py-3 text-[12px] leading-[1.6] text-[var(--bw-text-3)]">
                    <div class="mb-[5px] text-[11px] font-semibold uppercase tracking-[.5px] text-[var(--bw-text-2)]">{{ __('Cancellation policy') }}</div>
                    {{ $cancellationPolicyText }}
                </div>
                @else
                <div class="mt-[14px] text-[11px] leading-[1.6] text-[var(--bw-text-6)]">
                    By continuing you agree to the booking terms and privacy notice.
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
                        . ' · ' . __(':min min', ['min' => $reviewSelectedServices->sum('duration_minutes')]);
                } else {
                    $reviewServiceLabel = $selectedService
                        ? $selectedService->name . ' · ' . __(':min min', ['min' => $selectedService->duration_minutes])
                        : null;
                }
            @endphp
            <div>
                <div class="overflow-hidden rounded-xl border border-[var(--bw-border)]">
                    <div class="bg-[var(--bw-bg-muted)] px-[18px] py-0">
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
                            <div class="flex-shrink-0 text-[13px] text-[var(--bw-text-3)]">{{ $label }}</div>
                            <div class="text-right text-[13px] font-medium text-[var(--bw-text)]">{{ $value }}</div>
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
                    <div class="flex items-center justify-between gap-4 border-t-2 border-[var(--bw-border)] bg-[var(--bw-bg)] px-[18px] py-[14px]">
                        <div class="text-[13px] font-medium text-[var(--bw-text-2)]">{{ __('Total') }}</div>
                        <div class="text-[18px] font-bold text-[var(--bw-text)]">{{ $reviewTotalFormatted }}</div>
                    </div>
                    @endif
                </div>

                @php
                    $hasCustomAnswers = !empty($customFields) && array_filter($customAnswers, fn($v) => $v !== '' && $v !== null && $v !== false);
                @endphp
                @if ($hasCustomAnswers)
                <div class="mt-[14px] overflow-hidden rounded-xl border border-[var(--bw-border)]">
                    <div class="bg-[var(--bw-bg-muted)] px-[18px] py-0">
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
                            <div class="mb-1 text-[11px] font-medium uppercase tracking-[.4px] text-[var(--bw-text-3)]">{{ $field['label'] }}</div>
                            @if ($ft === 'signature')
                                <div class="max-w-[240px] overflow-hidden rounded-[6px] border border-[var(--bw-border)] bg-[var(--bw-bg)]">
                                    <img src="{{ $ans }}" alt="Signature" class="block max-h-[80px] w-full object-contain">
                                </div>
                            @elseif ($ft === 'checkbox')
                                <div class="text-[13px] font-medium text-[var(--bw-text)]">{{ $ans ? '✓ '.__('Yes') : '✗ '.__('No') }}</div>
                            @else
                                <div class="text-[13px] font-medium text-[var(--bw-text)]">{{ $displayAns }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-[14px] flex items-start gap-[10px] rounded-[10px] border px-[14px] py-3"
                     style="background:{{ $brandLight }}; border-color:{{ $brandBorder }};">
                    <svg class="mt-px flex-shrink-0" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $brand }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <div class="text-[12px] leading-[1.6] text-[var(--bw-text-2)]">
                        You'll get an email confirmation with a calendar invite. Cancel or reschedule any time up to 24h before.
                    </div>
                </div>
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
            <div class="px-8 py-[60px] text-center">
                <div class="mb-4 text-[14px] text-[var(--bw-text-3)]">{{ __("No active booking session.") }}</div>
                <a href="{{ url()->current() }}" class="text-[13px] font-semibold no-underline" style="color:{{ $brand }};">← {{ __('Start a booking') }}</a>
            </div>
            @else
            <div class="px-8 pb-9 pt-11 text-center">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full text-white"
                     style="background:{{ $brand }}; box-shadow: 0 0 0 12px {{ $brandLight }};">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="text-[26px] font-bold tracking-[-0.4px] text-[var(--bw-text)]">{{ $isDemo ? "You're booked!" : $successTitle }}</div>
                <div class="mx-auto mt-2 max-w-[360px] text-[13px] leading-[1.6] text-[var(--bw-text-3)]">
                    @if ($isDemo)
                        This is a demo — bookings are not saved.
                    @else
                        {{ $successBody }}
                    @endif
                </div>
                @if ($isDemo)
                <div class="mx-auto mt-4 flex max-w-[380px] items-center gap-2 rounded-lg border border-[#fde68a] bg-[#fffbeb] px-[14px] py-[10px] text-left text-[12px] text-[#92400e]">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ __('Demo mode — changes are not persisted') }}
                </div>
                @endif
                <div class="mx-auto mt-5 max-w-[400px] overflow-hidden rounded-xl border border-[var(--bw-border)] text-left">
                    <div class="bg-[var(--bw-bg-muted)] px-[18px] py-4">
                        @foreach ([
                            [__('Service'),   $successServiceName ?? null],
                            [__('Provider'),  $successProviderName],
                            [__('Date'),      $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('D, d M Y') : null],
                            [__('Time'),      $selectedStart],
                            [__('Booking #'), $isDemo ? __('DEMO') : ($bookingId ? '#'.$bookingId : null)],
                        ] as [$label, $value])
                        @if ($value)
                        <div class="flex items-baseline justify-between gap-3 border-b border-[var(--bw-border)] py-[6px]">
                            <div class="text-[12px] text-[var(--bw-text-3)]">{{ $label }}</div>
                            <div class="text-right text-[13px] font-medium text-[var(--bw-text)]">{{ $value }}</div>
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
                <div class="mx-auto mt-4 grid grid-cols-3 max-w-[380px] gap-[6px]">
                    @foreach ([['Google', $gcalUrl], ['Apple', $icalUrl], ['Outlook', $icalUrl]] as [$cal, $href])
                    <a href="{{ $href }}"
                       class="flex items-center justify-center gap-[5px] rounded-lg border border-[var(--bw-border)] bg-[var(--bw-bg)] px-[6px] py-[9px] text-center text-[12px] font-medium text-[var(--bw-text)] no-underline transition-all duration-[120ms] hover:bg-[var(--bw-bg-muted)]">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                        {{ $cal }}
                    </a>
                    @endforeach
                </div>
                @endif
                <div class="mx-auto mt-6 max-w-[380px]">
                    <button wire:click="$set('step', 1)"
                        class="w-full cursor-pointer rounded-lg border border-[var(--bw-border)] bg-transparent px-5 py-[11px] font-[inherit] text-[13px] font-medium text-[var(--bw-text-2)] transition-all duration-[120ms] hover:bg-[var(--bw-bg-muted)]">
                        {{ __('Book another') }}
                    </button>
                </div>
            </div>
            @endif
            @endif

        </div>

        {{-- ── Footer: Back · Continue ─────────────────────────── --}}
        @if ($step >= 2 && $step <= 5)
        <div class="lum-content-foot flex items-center gap-[10px] border-t border-[var(--bw-border)] bg-[var(--bw-bg-muted)] px-8 py-4">
            <button wire:click="goBack"
                class="inline-flex cursor-pointer items-center gap-[6px] rounded-lg border border-[var(--bw-border)] bg-transparent px-4 py-[9px] font-[inherit] text-[13px] font-medium text-[var(--bw-text-2)] transition-[background] duration-[120ms] hover:bg-[var(--bw-hover-bg)]">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                {{ __('Back') }}
            </button>

            <div class="flex-1"></div>

            @if ($step === 2)
            <button wire:click="continueFromProvider"
                class="inline-flex cursor-pointer items-center gap-2 border-0 px-6 py-[9px] font-[inherit] text-[13px] font-semibold text-white transition-opacity duration-[150ms]"
                style="background:{{ $brand }}; border-radius:{{ $btnRadius }};">
                {{ __('Continue') }}
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
            @endif

            @if ($step === 3)
            <button wire:click="continueFromSlot" @if(! $selectedStart) disabled @endif
                class="inline-flex cursor-pointer items-center gap-2 border-0 px-6 py-[9px] font-[inherit] text-[13px] font-semibold text-white transition-opacity duration-[150ms]"
                style="background:{{ $selectedStart ? $brand : 'var(--bw-border)' }}; opacity:{{ $selectedStart ? '1' : '0.6' }}; border-radius:{{ $btnRadius }};">
                {{ __('Continue') }}
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
            @endif

            @if ($step === 4)
            <button wire:click="continueToConfirm"
                    wire:loading.attr="disabled" wire:target="continueToConfirm"
                    wire:loading.class="opacity-60"
                class="inline-flex cursor-pointer items-center gap-2 border-0 px-6 py-[9px] font-[inherit] text-[13px] font-semibold text-white transition-opacity duration-[150ms]"
                style="background:{{ $brand }}; border-radius:{{ $btnRadius }};">
                <span wire:loading.remove wire:target="continueToConfirm" class="inline-flex items-center gap-[6px]">
                    {{ __('Continue') }}
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </span>
                <span wire:loading wire:target="continueToConfirm">{{ __('Checking…') }}</span>
            </button>
            @endif

            @if ($step === 5)
            <button wire:click="confirm" wire:loading.attr="disabled"
                class="inline-flex cursor-pointer items-center gap-2 border-0 px-7 py-[10px] font-[inherit] text-[14px] font-semibold text-white transition-opacity duration-[150ms]"
                style="background:{{ $brand }}; border-radius:{{ $btnRadius }};"
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

        {{-- Step 1 footer (continue inside content, but add compact bottom bar) --}}
        @if ($step === 1)
        <div class="lum-content-foot border-t border-[var(--bw-border)] bg-[var(--bw-bg-muted)] px-8 py-[14px]">
            @if ($serviceId)
            <button wire:click="continueFromService"
                class="flex w-full cursor-pointer items-center justify-center gap-2 border-0 px-5 py-[13px] font-[inherit] text-[14px] font-semibold text-white transition-opacity duration-[150ms] hover:opacity-[.88]"
                style="background:{{ $brand }}; border-radius:{{ $btnRadius }};">
                <span>{{ __('Continue') }} @if($allowMultipleServices && count($activeServiceIds) > 1)({{ count($activeServiceIds) }}) @endif</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
            @else
            <button disabled
                class="flex w-full cursor-not-allowed items-center justify-center rounded-[10px] border-0 bg-[var(--bw-disabled-bg)] px-5 py-[13px] font-[inherit] text-[14px] font-semibold text-[var(--bw-text-6)]">
                {{ $allowMultipleServices ? __('Select at least one service to continue') : __('Select a service to continue') }}
            </button>
            @endif
        </div>
        @endif

    </div>
</div>
