@php
    $firstBooking = $bookings->first();
    $tenant = $firstBooking?->tenant;
    $brand  = $tenant?->booking_page_color ?? '#7c3aed';
    $bHex   = ltrim($brand, '#');
    if (strlen($bHex) === 3) { $bHex = $bHex[0].$bHex[0].$bHex[1].$bHex[1].$bHex[2].$bHex[2]; }
    [$bR, $bG, $bB] = array_map('hexdec', str_split($bHex, 2));
    $initials = collect(explode(' ', $businessName))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
    $isPast ??= false;
@endphp

<section>
    {{-- Business header --}}
    <div class="flex items-center gap-3 mb-3">
        @if ($tenant?->logo)
            <img src="{{ asset('storage/' . $tenant->logo) }}"
                 alt="{{ $businessName }}"
                 class="h-8 w-8 rounded-lg object-contain border border-gray-200 bg-white">
        @else
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                 style="background: linear-gradient(135deg, {{ $brand }}, rgba({{ $bR }},{{ $bG }},{{ $bB }},0.7));">
                {{ $initials }}
            </div>
        @endif
        <div>
            <h2 class="text-sm font-semibold text-gray-800">{{ $businessName }}</h2>
            @if ($tenant?->slug && !$isPast)
                <a href="{{ route('booking.index', ['tenant' => $tenant->slug]) }}"
                   class="text-xs text-violet-600 hover:underline">
                    {{ __('Book again') }} →
                </a>
            @endif
        </div>
    </div>

    {{-- Booking cards --}}
    <div class="space-y-3">
        @foreach ($bookings as $booking)
        @php
            $isCancelled  = $booking->status === 'cancelled';
            $isCancellable = !$isPast
                && !$isCancelled
                && in_array($booking->status, ['pending', 'confirmed'])
                && $booking->cancellation_token;
        @endphp
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start justify-between gap-4 {{ ($isCancelled || $isPast) ? 'opacity-60' : '' }}"
             data-booking-id="{{ $booking->id }}">
            <div class="flex items-start gap-3 min-w-0">
                {{-- Date badge --}}
                <div class="flex-shrink-0 w-11 text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide"
                         style="color: {{ $brand }}">
                        {{ $booking->date->format('M') }}
                    </div>
                    <div class="text-lg font-bold text-gray-900 leading-none">
                        {{ $booking->date->format('d') }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $booking->date->format('D') }}
                    </div>
                </div>

                <div class="min-w-0">
                    <div class="font-semibold text-gray-900 truncate">
                        {{ $booking->service?->name ?? __('Appointment') }}
                    </div>
                    <div class="text-sm text-gray-500 mt-0.5">
                        {{ substr($booking->start_time, 0, 5) }}
                        @if ($booking->end_time)
                            – {{ substr($booking->end_time, 0, 5) }}
                        @endif
                    </div>

                    @if ($isCancellable)
                        {{-- Trigger cancel modal --}}
                        <button type="button"
                                class="text-xs text-red-500 hover:underline mt-1 inline-block cursor-pointer"
                                data-cancel-token="{{ $booking->cancellation_token }}"
                                data-cancel-label="{{ $booking->service?->name ?? __('Appointment') }} · {{ $booking->date->format('D d M') }} at {{ substr($booking->start_time, 0, 5) }}">
                            {{ __('Cancel') }}
                        </button>
                    @elseif ($isCancelled && $booking->cancellation_reason)
                        <p class="text-xs text-gray-400 mt-1 italic">
                            "{{ $booking->cancellation_reason }}"
                        </p>
                    @endif
                </div>
            </div>

            <span data-status-badge
                  class="flex-shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full
                {{ match($booking->status) {
                    'confirmed'  => 'bg-green-100 text-green-700',
                    'pending'    => 'bg-amber-100 text-amber-700',
                    'completed'  => 'bg-blue-100 text-blue-700',
                    'cancelled'  => 'bg-gray-100 text-gray-500',
                    'no_show'    => 'bg-red-100 text-red-500',
                    default      => 'bg-gray-100 text-gray-500',
                } }}">
                {{ __(\Illuminate\Support\Str::title($booking->status === 'no_show' ? 'No Show' : $booking->status)) }}
            </span>
        </div>
        @endforeach
    </div>
</section>
