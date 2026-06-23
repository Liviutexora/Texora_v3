@php
    $logo = \App\Models\Setting::get('site_logo');
    $logo = $logo ? 'storage/'.$logo : null;
    $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Slotara'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    @include('layouts.partials.head')
    @include('layouts.partials.css')
    @vite(['resources/css/app.css'])
    @stack('styles')
    <link rel="stylesheet" href="/font-proxy?family=Inter&weights=400;500;600;700;800;900">
</head>
<body class="min-h-screen bg-gray-50 flex">

    {{-- Left branding panel (hidden on mobile) --}}
    <div class="hidden lg:flex lg:w-[44%] xl:w-[42%] auth-panel-bg flex-col relative overflow-hidden">
        <div class="auth-dot-pattern absolute inset-0"></div>

        {{-- Top brand --}}
        <div class="relative z-10 p-10">
            <x-brand-logo mode="dark" :tagline="true" href="{{ route('home') }}" />
        </div>

        {{-- Middle content --}}
        <div class="relative z-10 flex-1 flex flex-col justify-center px-10 pb-16">
            <h2 class="text-3xl xl:text-4xl font-black text-white leading-tight mb-4">
                {{ __('The simplest way') }}<br>{{ __('to manage bookings') }}
            </h2>
            <p class="text-white/70 text-base leading-relaxed mb-10">
                {{ __('Set up your booking page in minutes. Accept appointments 24/7 — no back-and-forth needed.') }}
            </p>

            <div class="space-y-4">
                @foreach ([
                    [__('No login required for customers'), 'M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z'],
                    [__('Automatic email confirmations'), 'M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z'],
                    [__('Manage services & availability'), 'M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z'],
                    [__('Calendar export & reminders'), 'M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z'],
                ] as [$text, $path])
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $path }}" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-white/85 text-sm font-medium">{{ $text }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Decorative bottom blob --}}
        <div class="absolute -bottom-16 -right-16 w-64 h-64 rounded-full bg-white/5 border border-white/10"></div>
        <div class="absolute -bottom-4 -right-4 w-40 h-40 rounded-full bg-white/5 border border-white/10"></div>
    </div>

    {{-- Right form panel --}}
    <div class="flex-1 flex flex-col min-h-screen">
        {{-- Mobile top bar --}}
        <div class="lg:hidden flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white">
            <x-brand-logo size="sm" href="{{ route('home') }}" />
        </div>

        <div class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-[420px]">
                @yield('content')

                <div class="mt-6 text-center text-sm text-gray-500">
                    @yield('footer')
                </div>
                <p class="mt-4 text-center text-xs text-gray-400">&copy; {{ date('Y') }} {{ $siteName }}. {{ __('All rights reserved.') }}</p>
            </div>
        </div>
    </div>

@stack('scripts')
</body>
</html>
