@php
    $filSiteName    = \App\Models\Setting::get('site_name', config('app.name', 'Slotara'));
    $filUploadedLogo = \App\Models\Setting::get('site_logo');
@endphp

@if ($filUploadedLogo)
    {{-- Custom uploaded logo --}}
    <div class="fi-logo">
        <img src="{{ asset('storage/' . $filUploadedLogo) }}"
             alt="{{ $filSiteName }}"
             class="fil-logo-img"
             style="width: auto; max-width: 160px; object-fit: contain;">
    </div>
@else
    {{-- Default SVG mark + site name --}}
    <div class="fi-logo flex items-center gap-3" style="line-height:1;">
        <svg width="40" height="29" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <linearGradient id="sl-fil" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%"  stop-color="#6366f1"/>
                    <stop offset="100%" stop-color="#8b5cf6"/>
                </linearGradient>
            </defs>
            <line x1="3" y1="30" x2="53" y2="30" class="fil-tick" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="9"  y1="25.5" x2="9"  y2="30" class="fil-tick" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="18" y1="25.5" x2="18" y2="30" class="fil-tick" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="38" y1="25.5" x2="38" y2="30" class="fil-tick" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="47" y1="25.5" x2="47" y2="30" class="fil-tick" stroke-width="1.4" stroke-linecap="round"/>
            <rect x="25" y="8" width="6" height="22" rx="3" fill="url(#sl-fil)"/>
            <circle cx="28" cy="4" r="2" class="fil-dot"/>
        </svg>
        <div>
            <div class="fil-name" style="font-size:17px; font-weight:600; letter-spacing:-0.025em; font-feature-settings:'ss01','cv11';">
                {{ $filSiteName }}
            </div>
        </div>
    </div>
@endif
