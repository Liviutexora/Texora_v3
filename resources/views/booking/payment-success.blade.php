<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment successful') }} — {{ $booking->tenant?->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen antialiased flex items-center justify-center p-4">
<div class="bg-white rounded-xl shadow-sm border border-gray-200 max-w-md w-full p-8 text-center">
    <div class="inline-flex items-center justify-center w-14 h-14 bg-green-100 rounded-full mb-4">
        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h1 class="text-xl font-bold text-gray-900 mb-2">{{ __('Payment successful') }}</h1>
    <p class="text-gray-700 font-medium mb-1">{{ $booking->name }}</p>
    @if ($booking->service)
        <p class="text-gray-500 text-sm mb-4">{{ $booking->service->name }}</p>
    @endif
    <p class="text-gray-500 mb-6">
        {{ __('Your booking is confirmed. A confirmation has been sent to your email.') }}
    </p>

    <div class="flex flex-col gap-3">
        @if (! empty($receiptUrl))
            <a href="{{ $receiptUrl }}"
               class="inline-block bg-violet-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-violet-700 transition">
                {{ __('View receipt') }}
            </a>
        @endif
        <a href="{{ url('/' . $booking->tenant?->slug) }}"
           class="inline-block text-gray-600 hover:text-gray-900 text-sm">
            {{ __('Back to booking page') }}
        </a>
    </div>
</div>
</body>
</html>
