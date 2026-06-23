<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span style="display:inline-flex;align-items:center;gap:10px;">
                {{ __("Today's Schedule") }}
                <span style="font-size:12px;font-weight:500;color:#6b7280;">
                    {{ now()->format('l, d M Y') }}
                </span>
            </span>
        </x-slot>

        {{-- Utilization bar --}}
        @if($totalProviders > 0)
        <div style="margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="font-size:12px;color:#6b7280;">{{ __('Provider utilization today') }}</span>
                <span style="font-size:12px;font-weight:600;color:#374151;">
                    {{ $activeToday }} / {{ $totalProviders }} providers · {{ $utilizationPct }}%
                </span>
            </div>
            <div style="background:#e5e7eb;border-radius:9999px;height:6px;overflow:hidden;">
                <div style="height:6px;border-radius:9999px;width:{{ $utilizationPct }}%;background:{{ $utilizationPct >= 80 ? '#7c3aed' : ($utilizationPct >= 50 ? '#2563eb' : '#9ca3af') }};transition:width .3s;"></div>
            </div>
        </div>
        @endif

        @if($totalBookings === 0)
            <div style="text-align:center;padding:32px 0;color:#9ca3af;">
                <svg style="width:40px;height:40px;margin:0 auto 12px;display:block;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <p style="font-size:13px;">{{ __('No bookings scheduled for today.') }}</p>
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($bookings as $booking)
                <div style="background:{{ $booking->status === 'confirmed' ? 'rgba(5,150,105,0.05)' : 'rgba(245,158,11,0.05)' }};border:1px solid {{ $booking->status === 'confirmed' ? 'rgba(5,150,105,0.15)' : 'rgba(245,158,11,0.15)' }};display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:8px;">
                    {{-- Time --}}
                    <div style="min-width:52px;text-align:center;">
                        <div style="font-size:13px;font-weight:700;color:#111827;">
                            {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}
                        </div>
                        <div style="font-size:10px;color:#9ca3af;">
                            {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div style="width:1px;height:32px;background:#e5e7eb;flex-shrink:0;"></div>

                    {{-- Info --}}
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $booking->name }}
                        </div>
                        <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                            {{ $booking->service?->name ?? '—' }}
                            @if($booking->phone)
                                · {{ $booking->phone }}
                            @endif
                        </div>
                    </div>

                    {{-- Status badge --}}
                    <span style="background:{{ $booking->status === 'confirmed' ? 'rgba(5,150,105,0.1)' : 'rgba(245,158,11,0.1)' }};color:{{ $booking->status === 'confirmed' ? '#059669' : '#d97706' }};font-size:10px;font-weight:600;padding:3px 8px;border-radius:9999px;flex-shrink:0;">
                        {{ __(\Illuminate\Support\Str::title($booking->status === 'no_show' ? 'No Show' : $booking->status)) }}
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
