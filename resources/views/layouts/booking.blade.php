<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- SEO meta --}}
    <title>{{ $tenant?->name ?? config('app.name') }} — {{ __('Book an Appointment') }}</title>
    <meta name="description" content="{{ $tenant?->booking_page_tagline ? $tenant->booking_page_tagline . ' — ' . __('Book online instantly.') : __('Book your appointment online in seconds.') }}">
    <meta name="robots" content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="{{ $tenant?->name ?? config('app.name') }} — {{ __('Book an Appointment') }}">
    <meta property="og:description" content="{{ $tenant?->booking_page_tagline ?? __('Book your appointment online in seconds.') }}">
    @if ($tenant?->logo)
        <meta property="og:image" content="{{ asset('storage/' . $tenant->logo) }}">
    @endif

    {{-- Inter — self-hosted (font-face extracted to resources/css/components.css) --}}

    @php
        $brand       = $tenant?->booking_page_color ?? '#4f46e5';
        $font        = $bookingFont     ?? 'Inter';
        $darkForced  = $forceDarkMode   ?? false;
        $darkSystem  = $matchSystemTheme ?? true;
        $embedMode   = request()->query('embed'); // 'inline' | 'popup' | null

        // Decompose brand hex for rgba() usage in CSS
        $bHex = ltrim($brand, '#');
        if (strlen($bHex) === 3) {
            $bHex = $bHex[0].$bHex[0].$bHex[1].$bHex[1].$bHex[2].$bHex[2];
        }
        [$bR, $bG, $bB] = array_map('hexdec', str_split($bHex, 2));

        $googleFonts = [
            'Geist'          => '/font-proxy?family=Geist&weights=400;600;700',
            'Source Sans 3'  => '/font-proxy?family=Source+Sans+3&weights=400;600;700',
        ];
        $fontStack = match($font) {
            'Helvetica Neue' => "'Helvetica Neue', Helvetica, Arial, sans-serif",
            'Söhne'          => "'Söhne', 'Inter', ui-sans-serif, sans-serif",
            'Geist'          => "'Geist', 'Inter', ui-sans-serif, sans-serif",
            'Source Sans 3'  => "'Source Sans 3', ui-sans-serif, sans-serif",
            default          => "'Inter', ui-sans-serif, system-ui, sans-serif",
        };
    @endphp

    @if(isset($googleFonts[$font]))
        <link rel="stylesheet" href="{{ $googleFonts[$font] }}">
    @endif

    {{-- Brand colour + font + dark mode --}}
    <style>
        :root {
            --brand: {{ $brand }};
            --page-bg: #f0eee9;
            --page-header-bg: #fff;
            --page-header-border: #e7e7e4;
            --page-text: #18181b;
            --page-text-muted: #78716c;
        }
        html.page-dark {
            --page-bg: #111827;
            --page-header-bg: #1f2937;
            --page-header-border: #374151;
            --page-text: #f9fafb;
            --page-text-muted: #9ca3af;
        }
        @if($darkSystem)
        @media (prefers-color-scheme: dark) {
            html:not(.page-light) {
                --page-bg: #111827;
                --page-header-bg: #1f2937;
                --page-header-border: #374151;
                --page-text: #f9fafb;
                --page-text-muted: #9ca3af;
            }
        }
        @endif
        @if($darkForced)
        :root {
            --page-bg: #111827;
            --page-header-bg: #1f2937;
            --page-header-border: #374151;
            --page-text: #f9fafb;
            --page-text-muted: #9ca3af;
        }
        @endif
        body {
            font-family: {{ $fontStack }};
            background-color: var(--page-bg);
            /* Subtle brand halo at the very top of the page */
            background-image: radial-gradient(ellipse 160% 32% at 50% 0, rgba({{ $bR }},{{ $bG }},{{ $bB }},0.07) 0%, transparent 65%);
            color: var(--page-text);
        }
        code, .font-mono { font-family: ui-monospace, 'Cascadia Code', 'Segoe UI Mono', monospace; }
        @if($embedMode)
        /* Embed mode: transparent page, no scroll, compact card */
        body { background: transparent !important; }
        .booking-page-wrapper { padding: 8px !important; }
        @endif
    </style>
    <script>
    (function () {
        'use strict';
        @if($darkForced)
        document.documentElement.classList.add('page-dark');
        @elseif($darkSystem)
        var dark = false;
        try { if (localStorage.getItem('theme') === 'dark') dark = true; } catch (e) {}
        try { if (!dark && window.parent !== window) dark = window.parent.document.documentElement.classList.contains('dark'); } catch (e) {}
        if (dark) document.documentElement.classList.add('page-dark');
        else document.documentElement.classList.add('page-light');
        @endif
    })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen antialiased">

    <div class="min-h-screen flex flex-col">

        @if(!$embedMode)
        {{-- Header — hidden in embed/popup modes --}}
        <header class="border-b" style="background: var(--page-header-bg); border-color: var(--page-header-border);">
            <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
                @if ($tenant?->logo)
                    <img src="{{ asset('storage/' . $tenant->logo) }}"
                         alt="{{ $tenant->name }}"
                         class="h-9 w-auto object-contain rounded-lg">
                @else
                    @php
                        $initials = collect(explode(' ', $tenant?->name ?? 'S'))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
                    @endphp
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                         style="background: linear-gradient(135deg, {{ $brand }}, color-mix(in srgb, {{ $brand }} 70%, #000));">
                        {{ $initials }}
                    </div>
                @endif
                <div>
                    <div class="text-base font-semibold leading-tight" style="color:var(--page-text);">{{ $tenant?->name ?? config('app.name') }}</div>
                    @if ($tenant?->booking_page_tagline)
                        <div class="text-xs leading-tight mt-0.5" style="color:var(--page-text-muted);">{{ $tenant->booking_page_tagline }}</div>
                    @endif
                </div>
            </div>
        </header>
        @endif

        {{-- Main content --}}
        <main class="flex-1 max-w-2xl mx-auto w-full booking-page-wrapper {{ $embedMode ? 'px-2 py-2' : 'px-4 py-6 sm:py-10' }}">
            {{ $slot }}
        </main>

        @if(!$embedMode)
        {{-- Powered by footer — hidden in embed modes --}}
        @unless (config('app.hide_powered_by'))
        <footer class="text-center py-5 text-xs" style="color: var(--page-text-muted);">
            {{ __('Powered by') }} <span class="font-medium" style="color: var(--page-text-muted);">{{ config('app.name') }}</span>
        </footer>
        @endunless
        @endif

    </div>

    {{-- Auto-report height to parent when embedded --}}
    @if($embedMode)
    <script src="{{ asset('embed.js') }}" defer></script>
    @endif

    @livewireScripts
</body>
</html>
