@props([
    'mode'     => 'light',   // light | dark
    'size'     => 'md',      // sm | md | lg
    'tagline'  => false,
    'href'     => '/',
    'markOnly' => false,
])

@php
    $gradId = 'sl-' . substr(md5(uniqid()), 0, 8);

    // Mark dimensions (viewBox is 56×40)
    $markW = match($size) { 'sm' => 32, 'lg' => 56, default => 40 };
    $markH = match($size) { 'sm' => 23, 'lg' => 40, default => 29 };

    $isDark = $mode === 'dark';

    $tickStroke  = $isDark ? 'rgba(255,255,255,0.30)' : '#c7c4dc';
    $dotFill     = $isDark ? '#a78bfa' : '#8b5cf6';
    $nameClass   = $isDark ? 'text-white' : 'text-[#1e1b4b]';
    $tagClass    = $isDark ? 'text-[#a5b4fc]' : 'text-[#8e8aa6]';
    $nameSize    = match($size) { 'sm' => 'text-base', 'lg' => 'text-2xl', default => 'text-[18px]' };

    // Branding from settings
    $siteName     = \App\Models\Setting::get('site_name', config('app.name', 'Slotara'));
    $uploadedLogo = \App\Models\Setting::get('site_logo');
    $logoHeight   = (float) (\App\Models\Setting::get('site_admin_logo_height', 2) ?: 2);
    // Map size prop to a sensible rem height when no custom logo height is set
    $imgHeight    = match($size) { 'sm' => min($logoHeight, 1.5), 'lg' => $logoHeight + 0.5, default => $logoHeight };
@endphp

@if ($markOnly)
    {{-- Icon-only (used in footer, favicons, etc.) --}}
    @if ($uploadedLogo)
        <img src="{{ asset('storage/' . $uploadedLogo) }}"
             alt="{{ $siteName }}"
             style="height: {{ $imgHeight }}rem; width: auto; max-width: 120px; object-fit: contain;">
    @else
        <svg width="{{ $markW }}" height="{{ $markH }}" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <linearGradient id="{{ $gradId }}" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%"  stop-color="#6366f1"/>
                    <stop offset="100%" stop-color="#8b5cf6"/>
                </linearGradient>
            </defs>
            <line x1="3" y1="30" x2="53" y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="9"  y1="25.5" x2="9"  y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="18" y1="25.5" x2="18" y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="38" y1="25.5" x2="38" y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="47" y1="25.5" x2="47" y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <rect x="25" y="8" width="6" height="22" rx="3" fill="url(#{{ $gradId }})"/>
            <circle cx="28" cy="4" r="2" fill="{{ $dotFill }}"/>
        </svg>
    @endif
@elseif ($uploadedLogo)
    {{-- Custom uploaded logo — show image + optional tagline --}}
    <a href="{{ $href }}" class="inline-flex items-center gap-3.5">
        <img src="{{ asset('storage/' . $uploadedLogo) }}"
             alt="{{ $siteName }}"
             style="height: {{ $imgHeight }}rem; width: auto; max-width: 180px; object-fit: contain;">
        @if ($tagline)
            <span class="text-[9px] font-medium uppercase {{ $tagClass }}" style="letter-spacing: 0.28em;">
                {{ __('Appointment Booking Platform') }}
            </span>
        @endif
    </a>
@else
    {{-- Default SVG icon + site name lockup --}}
    <a href="{{ $href }}" class="inline-flex items-center gap-3.5">
        <svg width="{{ $markW }}" height="{{ $markH }}" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <linearGradient id="{{ $gradId }}" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%"  stop-color="#6366f1"/>
                    <stop offset="100%" stop-color="#8b5cf6"/>
                </linearGradient>
            </defs>
            <line x1="3" y1="30" x2="53" y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="9"  y1="25.5" x2="9"  y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="18" y1="25.5" x2="18" y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="38" y1="25.5" x2="38" y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="47" y1="25.5" x2="47" y2="30" stroke="{{ $tickStroke }}" stroke-width="1.4" stroke-linecap="round"/>
            <rect x="25" y="8" width="6" height="22" rx="3" fill="url(#{{ $gradId }})"/>
            <circle cx="28" cy="4" r="2" fill="{{ $dotFill }}"/>
        </svg>
        <div class="flex flex-col leading-none">
            <span
                class="{{ $nameSize }} font-semibold {{ $nameClass }}"
                style="letter-spacing: -0.025em; font-feature-settings: 'ss01', 'cv11';"
            >{{ $siteName }}</span>
            @if ($tagline)
                <span class="mt-2 text-[9px] font-medium uppercase {{ $tagClass }}" style="letter-spacing: 0.28em;">
                    {{ __('Appointment Booking Platform') }}
                </span>
            @endif
        </div>
    </a>
@endif
