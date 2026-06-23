<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment cancelled') }} — {{ $booking->tenant?->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen antialiased flex items-center justify-center p-4">
<div class="bg-white rounded-xl shadow-sm border border-gray-200 max-w-md w-full p-8 text-center">
    <div class="inline-flex items-center justify-center w-14 h-14 bg-amber-100 rounded-full mb-4">
        <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h1 class="text-xl font-bold text-gray-900 mb-2">{{ __('Payment cancelled') }}</h1>
    <p class="text-gray-500 mb-6">
        {{ __('Your booking was saved but payment was not completed. You can try again or contact the business.') }}
    </p>

    <a href="{{ url('/' . $booking->tenant?->slug) }}"
       class="inline-block bg-gray-900 text-white font-semibold px-6 py-3 rounded-xl hover:bg-gray-800 transition">
        {{ __('Return to booking') }}
    </a>
</div>
</body>
</html>
