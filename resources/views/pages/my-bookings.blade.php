<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('My Bookings') }} — {{ config('app.name', 'Slotara') }}</title>
    <meta name="description" content="{{ __('View all your upcoming and past appointments.') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="/font-proxy?family=Inter&weights=400;500;600;700;800;900">
    {{-- Styles extracted to resources/css/components.css --}}
</head>
<body class="min-h-screen bg-gray-50 antialiased">

    @if(session('impersonate_client_id') && auth()->user()?->hasRole('super_admin'))
    <div class="impersonation-bar">
        <div class="impersonation-bar-left">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#a5b4fc" style="width:16px;height:16px;flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
            <span class="impersonation-bar-badge">{{ __('Impersonating') }}</span>
            <span class="impersonation-bar-text">
                {{ __('Viewing as') }} <strong>{{ session('impersonate_client_name') }}</strong>
                <span style="color:#a5b4fc;margin-left:4px;">{{ session('impersonate_client_email') }}</span>
            </span>
        </div>
        <a href="{{ route('impersonate.client.exit') }}" class="impersonation-stop-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
            {{ __('Exit Client View') }}
        </a>
    </div>
    @endif

    @include('layouts.partials.front-nav', ['activePage' => ''])

    <main class="max-w-3xl mx-auto px-4 py-10 sm:py-14">

        {{-- Page header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('My Bookings') }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ __('Upcoming appointments for') }} <span class="font-medium text-gray-700">{{ $user->email }}</span>
            </p>
        </div>

        {{-- ── UPCOMING ─────────────────────────────────────────── --}}
        @if ($groupedUpcoming->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center mb-8">
                <div class="w-14 h-14 rounded-full bg-violet-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-800 mb-1">{{ __('No upcoming bookings') }}</h3>
                <p class="text-sm text-gray-400">{{ __('You have no upcoming appointments at the moment.') }}</p>
            </div>
        @else
            <div class="space-y-8 mb-8">
                @foreach ($groupedUpcoming as $businessName => $bookings)
                    @include('pages.partials.my-bookings-business', [
                        'businessName' => $businessName,
                        'bookings'     => $bookings,
                        'isPast'       => false,
                    ])
                @endforeach
            </div>
        @endif

        {{-- ── PAST BOOKINGS ────────────────────────────────────── --}}
        @if ($groupedPast->isNotEmpty())
        <div class="mt-2">
            <button id="sl-past-toggle"
                    class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors mb-4 cursor-pointer"
                    type="button">
                <span class="sl-past-chevron">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </span>
                {{ __('Past & cancelled bookings') }}
                <span class="text-xs bg-gray-100 text-gray-500 rounded-full px-2 py-0.5 font-semibold">
                    {{ $groupedPast->flatten()->count() }}
                </span>
            </button>

            <div id="sl-past-section" class="space-y-8">
                @foreach ($groupedPast as $businessName => $bookings)
                    @include('pages.partials.my-bookings-business', [
                        'businessName' => $businessName,
                        'bookings'     => $bookings,
                        'isPast'       => true,
                    ])
                @endforeach
            </div>
        </div>
        @endif

    </main>

    @include('layouts.partials.front-footer')

    {{-- ── CANCEL MODAL ────────────────────────────────────────── --}}
    <div id="sl-cancel-overlay" role="dialog" aria-modal="true" aria-labelledby="sl-cancel-title">
        <div id="sl-cancel-modal">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 id="sl-cancel-title" class="text-base font-semibold text-gray-900">{{ __('Cancel Booking') }}</h2>
                    <p id="sl-cancel-subtitle" class="text-sm text-gray-500 mt-0.5"></p>
                </div>
                <button id="sl-cancel-close" type="button"
                        class="text-gray-400 hover:text-gray-600 transition-colors ml-4 flex-shrink-0 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                {{ __('Reason for cancellation') }}
                <span class="text-gray-400 font-normal">({{ __('Optional') }})</span>
            </label>
            <textarea id="sl-cancel-reason" class="sl-cancel-reason"
                      rows="3" maxlength="500"
                      placeholder="{{ __('e.g. Change of plans, scheduling conflict…') }}"></textarea>

            <div id="sl-cancel-error" class="text-xs text-red-500 mt-1.5 hidden"></div>

            <div class="flex gap-3 mt-5">
                <button id="sl-cancel-confirm" type="button"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors cursor-pointer disabled:opacity-60">
                    {{ __('Yes, cancel booking') }}
                </button>
                <button id="sl-cancel-dismiss" type="button"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-lg transition-colors cursor-pointer">
                    {{ __('Keep it') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        window.slotlineI18n = {
            yesCancelBooking: @json(__('Yes, cancel booking')),
            cancelling:       @json(__('Cancelling…')),
            cancelled:        @json(__('Cancelled')),
            couldNotCancel:   @json(__('Could not cancel. Please try again.')),
            networkError:     @json(__('Network error. Please try again.')),
        };
    </script>
    @vite('resources/js/my-bookings.js')
</body>
</html>
