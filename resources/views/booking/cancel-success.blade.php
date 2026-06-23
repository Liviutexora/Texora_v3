<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Booking Cancelled') }} — {{ $booking->tenant?->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen antialiased flex items-center justify-center p-4">
<div class="bg-white rounded-xl shadow-sm border border-gray-200 max-w-md w-full p-8 text-center">
    <div class="inline-flex items-center justify-center w-14 h-14 bg-red-100 rounded-full mb-4">
        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </div>
    <h1 class="text-xl font-bold text-gray-900 mb-2">{{ __('Booking Cancelled') }}</h1>
    <p class="text-gray-500 mb-6">{{ __('Your booking has been cancelled. We hope to see you again.') }}</p>

    <a href="{{ url('/' . $booking->tenant?->slug) }}"
       class="inline-block bg-gray-900 text-white font-semibold px-6 py-3 rounded-xl hover:bg-gray-800 transition">
        {{ __('Book Again') }}
    </a>
</div>
</body>
</html>
