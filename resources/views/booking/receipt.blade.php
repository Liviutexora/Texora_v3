<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>{{ __('Receipt') }} #{{ $booking->id }} — {{ $booking->tenant?->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen antialiased">

{{-- ── Page wrapper ────────────────────────────────────────────────────────── --}}
<div class="max-w-2xl mx-auto my-10 px-4 no-print-margin">

    {{-- Print button --}}
    <div class="flex justify-end mb-4 no-print">
        <button data-action="print"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 shadow-sm transition-colors">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
            </svg>
            {{ __('Print') }}
        </button>
    </div>

    {{-- ── Receipt card ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">

        {{-- Top accent bar --}}
        <div class="h-1.5 bg-violet-600"></div>

        {{-- ── Header: logo + business info ──────────────────────────────── --}}
        <div class="flex items-start justify-between gap-6 px-8 pt-8 pb-6 border-b border-gray-100">

            {{-- Left: logo + name + contact --}}
            <div class="flex items-start gap-4 min-w-0">
                @if ($booking->tenant?->logo)
                <img src="{{ asset('storage/' . $booking->tenant->logo) }}"
                     alt="{{ $booking->tenant->name }}"
                     class="w-14 h-14 object-contain rounded-xl border border-gray-100 bg-gray-50 flex-shrink-0">
                @else
                <div class="w-14 h-14 rounded-xl bg-violet-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-xl font-bold">{{ strtoupper(substr($booking->tenant?->name ?? 'B', 0, 1)) }}</span>
                </div>
                @endif
                <div class="min-w-0">
                    <h1 class="text-lg font-bold text-gray-900 leading-tight">{{ $booking->tenant?->name }}</h1>
                    @if ($booking->tenant?->address || $booking->tenant?->city)
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ implode(', ', array_filter([$booking->tenant->address, $booking->tenant->city, $booking->tenant->country])) }}
                    </p>
                    @endif
                    @if ($booking->tenant?->phone)
                    <p class="text-sm text-gray-500">{{ $booking->tenant->phone }}</p>
                    @endif
                    @if ($booking->tenant?->email)
                    <p class="text-sm text-gray-500">{{ $booking->tenant->email }}</p>
                    @endif
                    @if ($booking->tenant?->website_url)
                    <p class="text-sm text-gray-500">{{ $booking->tenant->website_url }}</p>
                    @endif
                </div>
            </div>

            {{-- Right: receipt label + number + date --}}
            <div class="text-right flex-shrink-0">
                <div class="text-xs font-semibold uppercase tracking-widest text-violet-600 mb-1">{{ __('Receipt') }}</div>
                <div class="text-2xl font-bold text-gray-900">#{{ $booking->id }}</div>
                <div class="text-sm text-gray-400 mt-1">
                    {{ now()->timezone($booking->tenant?->timezone ?? config('app.timezone'))->format('d M Y') }}
                </div>
            </div>
        </div>

        {{-- ── Bill to + appointment ──────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-6 px-8 py-6 border-b border-gray-100">

            {{-- Bill to --}}
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 mb-2">{{ __('Bill to') }}</div>
                <p class="font-semibold text-gray-900">{{ $booking->name }}</p>
                @if ($booking->email)
                <p class="text-sm text-gray-500 mt-0.5">{{ $booking->email }}</p>
                @endif
                @if ($booking->phone)
                <p class="text-sm text-gray-500">{{ $booking->phone }}</p>
                @endif
            </div>

            {{-- Appointment --}}
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 mb-2">{{ __('Appointment') }}</div>
                @if ($booking->date)
                <p class="font-semibold text-gray-900">{{ $booking->date->format('l, d M Y') }}</p>
                @endif
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ substr($booking->start_time ?? '', 0, 5) }}
                    @if ($booking->end_time) – {{ substr($booking->end_time, 0, 5) }} @endif
                </p>
                @if ($booking->providerRelation?->user)
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ __('with') }} <span class="font-medium text-gray-700">{{ $booking->providerRelation->user->name }}</span>
                </p>
                @endif
            </div>
        </div>

        {{-- ── Line items ─────────────────────────────────────────────────── --}}
        <div class="px-8 py-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left text-[11px] font-semibold uppercase tracking-widest text-gray-400 pb-3">{{ __('Description') }}</th>
                        <th class="text-right text-[11px] font-semibold uppercase tracking-widest text-gray-400 pb-3">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-50">
                        <td class="py-4">
                            <p class="font-semibold text-gray-900">{{ $booking->service?->name }}</p>
                            @if ($booking->date)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $booking->date->format('d M Y') }}, {{ substr($booking->start_time ?? '', 0, 5) }}</p>
                            @endif
                        </td>
                        <td class="py-4 text-right font-semibold text-gray-900 whitespace-nowrap">
                            {{ strtoupper($booking->currency ?? 'USD') }}
                            {{ number_format((float) ($booking->amount ?? 0), 2) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="pt-4 text-sm font-semibold text-gray-700">{{ __('Total') }}</td>
                        <td class="pt-4 text-right text-lg font-bold text-gray-900 whitespace-nowrap">
                            {{ strtoupper($booking->currency ?? 'USD') }}
                            {{ number_format((float) ($booking->amount ?? 0), 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ── Payment details ─────────────────────────────────────────────── --}}
        <div class="mx-8 mb-8 rounded-xl border border-gray-100 bg-gray-50 divide-y divide-gray-100 text-sm overflow-hidden">

            {{-- Status row --}}
            <div class="flex items-center justify-between px-5 py-3">
                <span class="text-gray-500 font-medium">{{ __('Payment status') }}</span>
                @php $status = $booking->payment_status ?? 'pending'; @endphp
                <span @class([
                    'inline-flex items-center gap-1.5 font-semibold text-xs px-2.5 py-1 rounded-full',
                    'bg-emerald-100 text-emerald-700' => $status === 'paid',
                    'bg-amber-100 text-amber-700'     => $status === 'pending',
                    'bg-red-100 text-red-700'         => $status === 'failed',
                    'bg-gray-100 text-gray-600'       => !in_array($status, ['paid','pending','failed']),
                ])>
                    <span class="w-1.5 h-1.5 rounded-full @if($status==='paid') bg-emerald-500 @elseif($status==='pending') bg-amber-500 @elseif($status==='failed') bg-red-500 @else bg-gray-400 @endif"></span>
                    {{ ucfirst($status) }}
                </span>
            </div>

            @if ($booking->payment_gateway)
            <div class="flex items-center justify-between px-5 py-3">
                <span class="text-gray-500 font-medium">{{ __('Payment method') }}</span>
                <span class="text-gray-800 font-medium capitalize">{{ str_replace('_', ' ', $booking->payment_gateway) }}</span>
            </div>
            @endif

            @if ($booking->payment_reference)
            <div class="flex items-center justify-between px-5 py-3">
                <span class="text-gray-500 font-medium">{{ __('Reference') }}</span>
                <span class="font-mono text-xs text-gray-700 bg-white border border-gray-200 rounded px-2 py-0.5">{{ $booking->payment_reference }}</span>
            </div>
            @endif

            @if ($booking->paid_at)
            <div class="flex items-center justify-between px-5 py-3">
                <span class="text-gray-500 font-medium">{{ __('Paid at') }}</span>
                <span class="text-gray-800">{{ $booking->paid_at->timezone($booking->tenant?->timezone ?? config('app.timezone'))->format('d M Y, H:i') }}</span>
            </div>
            @endif
        </div>

        {{-- ── Footer ───────────────────────────────────────────────────────── --}}
        <div class="border-t border-gray-100 px-8 py-5 flex items-center justify-between gap-4 bg-gray-50">
            <p class="text-xs text-gray-400">{{ __('Thank you for your booking.') }}</p>
            @if ($booking->tenant?->website_url)
            <a href="{{ $booking->tenant->website_url }}" class="text-xs text-violet-500 hover:underline">{{ $booking->tenant->website_url }}</a>
            @endif
        </div>

    </div>{{-- end receipt card --}}
</div>{{-- end page wrapper --}}

<script>
(function () {
    'use strict';
    document.querySelectorAll('[data-action="print"]').forEach(function (btn) {
        btn.addEventListener('click', function () { window.print(); });
    });
}());
</script>
</body>
</html>
