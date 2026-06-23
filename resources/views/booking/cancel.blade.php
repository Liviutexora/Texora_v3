<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Cancel Booking') }} — {{ $booking->tenant?->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen antialiased flex items-center justify-center p-4">
<div class="bg-white rounded-xl shadow-sm border border-gray-200 max-w-md w-full p-8">
    <h1 class="text-xl font-bold text-gray-900 mb-1">{{ __('Cancel Booking') }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ $booking->tenant?->name }}</p>

    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm space-y-1">
        <div class="flex justify-between"><span class="text-gray-500">{{ __('Service') }}</span><span class="font-medium">{{ $booking->service?->name }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">{{ __('Date') }}</span><span class="font-medium">{{ $booking->date->format('D, d M Y') }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">{{ __('Time') }}</span><span class="font-medium">{{ substr($booking->start_time, 0, 5) }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">{{ __('Name') }}</span><span class="font-medium">{{ $booking->name }}</span></div>
    </div>

    <p class="text-sm text-gray-700 mb-6">{{ __('Are you sure you want to cancel this booking? This action cannot be undone.') }}</p>

    <form method="POST" action="{{ route('booking.cancel.confirm', $booking->cancellation_token) }}">
        @csrf
        <button type="submit"
            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl transition">
            {{ __('Yes, Cancel My Booking') }}
        </button>
    </form>

    <a href="{{ url('/' . $booking->tenant?->slug) }}"
       class="block text-center mt-4 text-sm text-gray-500 hover:underline">
        {{ __('Keep my booking') }}
    </a>
</div>
</body>
</html>
