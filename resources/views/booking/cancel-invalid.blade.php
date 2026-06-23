<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Link Expired or Invalid') }} — {{ $booking->tenant?->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen antialiased flex items-center justify-center p-4">
<div class="bg-white rounded-xl shadow-sm border border-gray-200 max-w-md w-full p-8 text-center">
    <h1 class="text-xl font-bold text-gray-900 mb-2">{{ __('Link Expired or Invalid') }}</h1>
    <p class="text-gray-500 mb-6">{{ __('This cancellation link has already been used or is no longer valid. Your booking status is :status.', ['status' => $booking->status]) }}</p>
    <a href="{{ url('/' . $booking->tenant?->slug) }}"
       class="inline-block bg-gray-900 text-white font-semibold px-6 py-3 rounded-xl hover:bg-gray-800 transition">
        {{ __('Back to Booking Page') }}
    </a>
</div>
</body>
</html>
