@php
    $brand = $tenant?->booking_page_color ?? '#7c3aed';
    $hex = ltrim($brand, '#');
    if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
    [$br, $bg, $bb] = array_map('hexdec', str_split($hex, 2));
    $brandLight = "rgba($br,$bg,$bb,0.08)";
@endphp
<div class="max-w-lg mx-auto">
    <h2 class="text-xl font-bold text-gray-800 mb-2">{{ __('My Upcoming Bookings') }}</h2>
    <p class="text-sm text-gray-500 mb-6">{{ __('Enter your email address to find your bookings.') }}</p>

    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
        <div class="flex gap-3">
            <input type="email" wire:model="email" wire:keydown.enter="search"
                   placeholder="{{ __('your@email.com') }}"
                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2">
            <button wire:click="search" wire:loading.attr="disabled"
                    class="text-white font-semibold px-5 py-2 rounded-lg transition disabled:opacity-60 text-sm"
                    style="background-color: {{ $brand }}">
                <span wire:loading.remove>{{ __('Search') }}</span>
                <span wire:loading>…</span>
            </button>
        </div>
        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    @if ($searched)
        @if ($bookings->isEmpty())
            <p class="text-center text-gray-400 py-8">{{ __('No upcoming bookings found for') }} <strong>{{ $email }}</strong>.</p>
        @else
            <div class="space-y-3">
                @foreach ($bookings as $booking)
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $booking->service?->name }}</div>
                            <div class="text-sm text-gray-500 mt-0.5">
                                {{ $booking->date->format('D, d M Y') }} at {{ substr($booking->start_time, 0, 5) }}
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full
                            {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                    @if ($booking->cancellation_token)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ route('booking.cancel', $booking->cancellation_token) }}"
                           class="text-sm text-red-600 hover:underline">
                            {{ __('Cancel this booking') }}
                        </a>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
