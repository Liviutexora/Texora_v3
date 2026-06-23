<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Slotara') }} — {{ __('Book appointments') }}</title>
    <meta name="description" content="{{ __('Find a business and book your slot in seconds. No account needed — just pick a time and you\'re all set.') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="/font-proxy?family=Inter&weights=400;500;600;700;800;900">
</head>
<body class="flex flex-col min-h-screen bg-[#fafafa]">

    <div class="hero-bg flex-1 flex flex-col">
        {{-- Dot overlay --}}
        <div class="dot-pattern fixed inset-0 opacity-40 pointer-events-none"></div>

        {{-- Nav --}}
        @include('layouts.partials.front-nav', ['activePage' => 'clients'])

        {{-- Hero --}}
        <section class="relative z-10 flex flex-col items-center px-6 pt-16 pb-20 sm:pt-24 sm:pb-28 text-center">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 bg-violet-50 border border-violet-200 text-violet-700 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-8 uppercase tracking-widest">
                <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></span>
                {{ __('Online Booking Platform') }}
            </div>

            {{-- Headline --}}
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-gray-900 leading-[1.05] max-w-3xl tracking-tight">
                {{ __('Book appointments') }}<br>
                <span class="gradient-text">{{ __('effortlessly') }}</span>
            </h1>

            <p class="mt-6 text-lg sm:text-xl text-gray-500 max-w-lg leading-relaxed">
                {{ __('Find a business and book your slot in seconds.') }}<br class="hidden sm:block">
                {{ __("No account needed — just pick a time and you're all set.") }}
            </p>

            {{-- Slug search --}}
            <form method="GET" action="#" id="slug-form" class="mt-10 w-full max-w-lg" onsubmit="handleSlugSearch(event)">
                <div class="slug-input-wrap flex items-center rounded-2xl border border-gray-200 bg-white shadow-md p-1.5 gap-2">
                    <div class="flex items-center flex-1 min-w-0 pl-3">
                        <span class="text-gray-400 text-sm select-none whitespace-nowrap font-medium flex-shrink-0">
                            {{ parse_url(config('app.url'), PHP_URL_HOST) }}/
                        </span>
                        <input
                            id="tenant-slug"
                            type="text"
                            placeholder="{{ __('your-business') }}"
                            class="flex-1 min-w-0 py-2 px-1 text-sm text-gray-900 bg-transparent border-0 outline-none placeholder-gray-400"
                            autocomplete="off"
                            spellcheck="false"
                        >
                    </div>
                    <button type="submit" class="flex-shrink-0 bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold px-6 py-3 rounded-xl transition-colors whitespace-nowrap glow-btn">
                        {{ __('Book now') }} &rarr;
                    </button>
                </div>
                <p id="slug-error" class="mt-2 text-xs text-red-500 hidden">{{ __('Please enter a business name.') }}</p>
            </form>

            {{-- Feature pills --}}
            <div class="mt-7 flex flex-wrap justify-center gap-2.5">
                @foreach ([
                    [__('No login required'), 'text-violet-500'],
                    [__('Instant confirmation'), 'text-emerald-500'],
                    [__('Email reminders'), 'text-blue-500'],
                    [__('Calendar export'), 'text-orange-500'],
                ] as [$feat, $color])
                    <span class="feature-pill flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-1.5 shadow-sm text-sm text-gray-600 font-medium">
                        <svg class="w-3.5 h-3.5 {{ $color }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                        </svg>
                        {{ $feat }}
                    </span>
                @endforeach
            </div>
        </section>

        {{-- How it works --}}
        <section class="relative z-10 w-full max-w-5xl mx-auto px-6 pb-24">
            <div class="text-center mb-12">
                <p class="text-xs font-bold text-violet-600 uppercase tracking-widest mb-3">{{ __('How it works') }}</p>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('Book in 3 simple steps') }}</h2>
                <p class="mt-3 text-gray-500 max-w-sm mx-auto text-sm leading-relaxed">{{ __('No registration, no hassle. From search to confirmed in under a minute.') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                @foreach ([
                    [
                        'num'   => '01',
                        'title' => __('Find your business'),
                        'desc'  => __('Enter the business URL above or type the name of the service provider you want to book.'),
                        'bg'    => 'bg-violet-50',
                        'icon_bg' => 'bg-violet-100',
                        'icon_color' => 'text-violet-600',
                        'num_color'  => 'text-violet-200',
                        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>',
                    ],
                    [
                        'num'   => '02',
                        'title' => __('Pick a time slot'),
                        'desc'  => __('Browse available times and select the date that works best for you — no back-and-forth.'),
                        'bg'    => 'bg-indigo-50',
                        'icon_bg' => 'bg-indigo-100',
                        'icon_color' => 'text-indigo-600',
                        'num_color'  => 'text-indigo-200',
                        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                    ],
                    [
                        'num'   => '03',
                        'title' => __('Get confirmed instantly'),
                        'desc'  => __('Receive an instant confirmation by email with all your booking details and reminders.'),
                        'bg'    => 'bg-emerald-50',
                        'icon_bg' => 'bg-emerald-100',
                        'icon_color' => 'text-emerald-600',
                        'num_color'  => 'text-emerald-200',
                        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                    ],
                ] as $step)
                <div class="step-card relative {{ $step['bg'] }} rounded-2xl p-6 border border-white/80">
                    <div class="absolute top-5 right-5 text-5xl font-black {{ $step['num_color'] }} leading-none select-none">
                        {{ $step['num'] }}
                    </div>
                    <div class="w-11 h-11 {{ $step['icon_bg'] }} rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-5 h-5 {{ $step['icon_color'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            {!! $step['icon'] !!}
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">{{ $step['title'] }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </section>

        {{-- Business owner CTA --}}
        <section class="relative z-10 w-full max-w-4xl mx-auto px-6 pb-24">
            <div class="relative bg-gradient-to-br from-violet-600 to-indigo-600 rounded-3xl overflow-hidden shadow-2xl shadow-violet-200/60">
                {{-- Background decoration --}}
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 24px 24px;"></div>
                <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/5"></div>
                <div class="absolute -bottom-8 -left-8 w-32 h-32 rounded-full bg-white/5"></div>

                <div class="relative px-8 py-10 sm:px-12 sm:py-12 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-8">
                    <div class="text-left max-w-sm">
                        <p class="text-xs font-bold text-violet-200 uppercase tracking-widest mb-3">{{ __('For business owners') }}</p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-white leading-snug">{{ __('Ready to grow your bookings?') }}</h2>
                        <p class="mt-3 text-sm text-violet-100 leading-relaxed">{{ __('Set up your booking page in minutes. Manage services, availability, and customers from one dashboard.') }}</p>
                    </div>
                    <div class="flex flex-col items-start sm:items-end gap-3 flex-shrink-0">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-white hover:bg-violet-50 text-violet-700 text-sm font-bold px-7 py-3.5 rounded-xl transition-all duration-200 whitespace-nowrap shadow-lg">
                            {{ __('See how it works') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                        <p class="text-xs text-violet-200 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                            {{ __('No credit card required') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        @include('layouts.partials.front-footer')
    </div>

    @vite('resources/js/welcome.js')
</body>
</html>
