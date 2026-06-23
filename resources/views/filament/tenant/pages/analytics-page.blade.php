<x-filament-panels::page>

    {{-- ── Period Filter ─────────────────────────────────────────────────── --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:4px;">
        <p style="font-size:0.9375rem; font-weight:600; color:#374151; margin:0;">{{ $periodLabel }}</p>
        <div style="display:flex; flex-wrap:wrap; gap:6px;">
            @foreach ([
                'this_month'    => __('This Month'),
                'last_month'    => __('Last Month'),
                'last_3_months' => __('Last 3 Months'),
                'last_6_months' => __('Last 6 Months'),
                'this_year'     => __('This Year'),
            ] as $value => $label)
                <button
                    wire:click="$set('period', '{{ $value }}')"
                    style="
                        padding: 6px 14px;
                        font-size: 0.75rem;
                        font-weight: 500;
                        border-radius: 9999px;
                        border: none;
                        cursor: pointer;
                        transition: all 0.15s;
                        {{ $period === $value
                            ? 'background:#7c3aed; color:#fff; box-shadow:0 1px 3px rgba(124,58,237,0.3);'
                            : 'background:#f3f4f6; color:#6b7280;' }}
                    "
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ── Core Stats ─────────────────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:16px;">

        {{-- Total Bookings --}}
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px;">
            <p style="font-size:0.7rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 4px;">{{ __('Total Bookings') }}</p>
            <p style="font-size:2rem; font-weight:700; color:#111827; margin:0 0 2px; line-height:1.1;">{{ number_format($total) }}</p>
            <p style="font-size:0.75rem; color:#d1d5db; margin:0;">{{ $pending }} {{ __('pending confirmation') }}</p>
        </div>

        {{-- Revenue --}}
        <div style="background:#f5f3ff; border:1px solid #ede9fe; border-radius:12px; padding:16px 20px;">
            <p style="font-size:0.7rem; font-weight:600; color:#7c3aed; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 4px;">{{ __('Revenue') }}</p>
            <p style="font-size:2rem; font-weight:700; color:#5b21b6; margin:0 0 2px; line-height:1.1;">{{ $currencySymbol }}{{ number_format($revenue, 0) }}</p>
            <p style="font-size:0.75rem; color:#a78bfa; margin:0;">{{ $currency }} · {{ $paidCount }} {{ __('paid') }} · {{ __('avg') }} {{ $currencySymbol }}{{ number_format($avgValue, 0) }}</p>
        </div>

        {{-- Confirm Rate --}}
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px 20px;">
            <p style="font-size:0.7rem; font-weight:600; color:#16a34a; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 4px;">{{ __('Confirm Rate') }}</p>
            <p style="font-size:2rem; font-weight:700; color:#15803d; margin:0 0 2px; line-height:1.1;">{{ $confirmRate }}%</p>
            <p style="font-size:0.75rem; color:#86efac; margin:0;">{{ $confirmed }} {{ __('confirmed + completed') }}</p>
        </div>

        {{-- Cancel / No-show --}}
        <div style="background:{{ $cancelRate > 20 ? '#fef2f2' : '#fffbeb' }}; border:1px solid {{ $cancelRate > 20 ? '#fecaca' : '#fde68a' }}; border-radius:12px; padding:16px 20px;">
            <p style="font-size:0.7rem; font-weight:600; color:{{ $cancelRate > 20 ? '#dc2626' : '#d97706' }}; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 4px;">{{ __('Cancellation') }}</p>
            <p style="font-size:2rem; font-weight:700; color:{{ $cancelRate > 20 ? '#b91c1c' : '#b45309' }}; margin:0 0 2px; line-height:1.1;">{{ $cancelRate }}%</p>
            <p style="font-size:0.75rem; color:#d1d5db; margin:0;">{{ $cancelled }} {{ __('cancelled') }} · {{ $noShowRate }}% {{ __('no-show') }} ({{ $noShow }})</p>
        </div>

    </div>

    {{-- ── Booking Trend Chart ─────────────────────────────────────────────── --}}
    @livewire(\App\Filament\Tenant\Widgets\BookingsTrendChart::class)

    {{-- ── Revenue by Service + Busiest Days ─────────────────────────────── --}}
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
        @livewire(\App\Filament\Tenant\Widgets\RevenueByServiceChart::class)
        @livewire(\App\Filament\Tenant\Widgets\BookingsByDayOfWeekChart::class)
    </div>

    {{-- ── Client Breakdown + Peak Hours ─────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">

        {{-- New vs Returning Clients --}}
        <x-filament::section>
            <x-slot name="heading">{{ __('Client Breakdown') }}</x-slot>
            <x-slot name="description">{{ __('New vs returning') }} · {{ $periodLabel }}</x-slot>

            @php
                $totalClients = $newCount + $returningCount;
                $newPct       = $totalClients > 0 ? round($newCount / $totalClients * 100) : 0;
                $retPct       = $totalClients > 0 ? round($returningCount / $totalClients * 100) : 0;
            @endphp

            @if ($totalClients === 0)
                <div style="padding:32px 0; text-align:center; font-size:0.875rem; color:#9ca3af;">{{ __('No client data for this period.') }}</div>
            @else
                <div style="display:flex; flex-direction:column; gap:20px; padding-top:4px;">

                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:6px;">
                            <span style="font-size:0.875rem; font-weight:500; color:#374151;">{{ __('New Clients') }}</span>
                            <span style="font-size:0.875rem; font-weight:600; color:#7c3aed;">{{ $newCount }} <span style="font-size:0.75rem; font-weight:400; color:#9ca3af;">({{ $newPct }}%)</span></span>
                        </div>
                        <div style="height:8px; border-radius:9999px; background:#f3f4f6; overflow:hidden;">
                            <div style="height:100%; border-radius:9999px; background:#7c3aed; width:{{ $newPct }}%; transition:width 0.5s;"></div>
                        </div>
                    </div>

                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:6px;">
                            <span style="font-size:0.875rem; font-weight:500; color:#374151;">{{ __('Returning Clients') }}</span>
                            <span style="font-size:0.875rem; font-weight:600; color:#059669;">{{ $returningCount }} <span style="font-size:0.75rem; font-weight:400; color:#9ca3af;">({{ $retPct }}%)</span></span>
                        </div>
                        <div style="height:8px; border-radius:9999px; background:#f3f4f6; overflow:hidden;">
                            <div style="height:100%; border-radius:9999px; background:#10b981; width:{{ $retPct }}%; transition:width 0.5s;"></div>
                        </div>
                    </div>

                    <p style="font-size:0.75rem; color:#9ca3af; margin:0;">{{ $totalClients }} {{ __('unique clients this period') }}</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Peak Hours --}}
        <x-filament::section>
            <x-slot name="heading">{{ __('Peak Booking Hours') }}</x-slot>
            <x-slot name="description">{{ __('Bookings by hour') }} · {{ $periodLabel }}</x-slot>

            @php $maxHour = max(array_merge($peakHours, [1])); @endphp

            @if (array_sum($peakHours) === 0)
                <div style="padding:32px 0; text-align:center; font-size:0.875rem; color:#9ca3af;">{{ __('No hourly data for this period.') }}</div>
            @else
                <div style="display:flex; align-items:flex-end; gap:3px; height:80px; margin-top:8px;">
                    @foreach ($peakHours as $i => $count)
                        @php
                            $pct = round($count / $maxHour * 100);
                            $isTop = $count === $maxHour && $count > 0;
                        @endphp
                        <div
                            title="{{ $peakHourLabels[$i] }}: {{ $count }}"
                            style="
                                flex: 1;
                                height: {{ max($pct, 6) }}%;
                                border-radius: 3px 3px 0 0;
                                background: {{ $isTop ? '#7c3aed' : '#c4b5fd' }};
                                min-height: 5px;
                            "
                        ></div>
                    @endforeach
                </div>
                <div style="display:flex; gap:3px; margin-top:4px;">
                    @foreach ($peakHourLabels as $i => $label)
                        <div style="flex:1; text-align:center; font-size:9px; color:#9ca3af; {{ $i % 2 !== 0 ? 'opacity:0' : '' }}">{{ substr($label, 0, 2) }}</div>
                    @endforeach
                </div>
                @php
                    $topHourIdx   = array_search($maxHour, $peakHours);
                    $topHourLabel = $peakHourLabels[$topHourIdx] ?? '';
                @endphp
                @if ($maxHour > 0)
                    <p style="font-size:0.75rem; color:#9ca3af; margin:8px 0 0;">{{ __('Busiest:') }} <strong style="color:#374151;">{{ $topHourLabel }}</strong> &mdash; {{ $maxHour }} {{ __('bookings') }}</p>
                @endif
            @endif
        </x-filament::section>

    </div>

    {{-- ── Top Providers + Top Services ──────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">

        {{-- Top Providers --}}
        <x-filament::section>
            <x-slot name="heading">{{ __('Top Providers') }}</x-slot>
            <x-slot name="description">{{ __('By bookings') }} · {{ $periodLabel }}</x-slot>

            @if ($topProviders->isEmpty())
                <div style="padding:32px 0; text-align:center; font-size:0.875rem; color:#9ca3af;">{{ __('No provider data for this period.') }}</div>
            @else
                <div style="display:flex; flex-direction:column; gap:14px; padding-top:4px;">
                    @foreach ($topProviders as $i => $row)
                        @php $barPct = round($row->bookings / $maxProviderBookings * 100); @endphp
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span style="
                                width:24px; height:24px; border-radius:50%;
                                display:flex; align-items:center; justify-content:center;
                                font-size:0.7rem; font-weight:700; flex-shrink:0;
                                {{ $i === 0 ? 'background:#7c3aed; color:#fff;' : 'background:#e5e7eb; color:#6b7280;' }}
                            ">{{ $i + 1 }}</span>
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:4px;">
                                    <span style="font-size:0.875rem; font-weight:500; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $row->name }}</span>
                                    <span style="font-size:0.875rem; font-weight:600; color:#374151; margin-left:8px; flex-shrink:0;">{{ $row->bookings }}</span>
                                </div>
                                <div style="height:5px; border-radius:9999px; background:#f3f4f6; overflow:hidden;">
                                    <div style="height:100%; border-radius:9999px; background:#a78bfa; width:{{ $barPct }}%;"></div>
                                </div>
                                @if ($row->revenue > 0)
                                    <p style="font-size:0.7rem; color:#9ca3af; margin:3px 0 0;">{{ $currencySymbol }}{{ number_format($row->revenue, 0) }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Top Services --}}
        <x-filament::section>
            <x-slot name="heading">{{ __('Top Services') }}</x-slot>
            <x-slot name="description">{{ __('By bookings') }} · {{ $periodLabel }}</x-slot>

            @if ($topServices->isEmpty())
                <div style="padding:32px 0; text-align:center; font-size:0.875rem; color:#9ca3af;">{{ __('No service data for this period.') }}</div>
            @else
                <div style="display:flex; flex-direction:column; gap:14px; padding-top:4px;">
                    @foreach ($topServices as $i => $row)
                        @php $barPct = round($row->bookings / $maxServiceBookings * 100); @endphp
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span style="
                                width:24px; height:24px; border-radius:50%;
                                display:flex; align-items:center; justify-content:center;
                                font-size:0.7rem; font-weight:700; flex-shrink:0;
                                {{ $i === 0 ? 'background:#059669; color:#fff;' : 'background:#e5e7eb; color:#6b7280;' }}
                            ">{{ $i + 1 }}</span>
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:4px;">
                                    <span style="font-size:0.875rem; font-weight:500; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $row->name }}</span>
                                    <span style="font-size:0.875rem; font-weight:600; color:#374151; margin-left:8px; flex-shrink:0;">{{ $row->bookings }}</span>
                                </div>
                                <div style="height:5px; border-radius:9999px; background:#f3f4f6; overflow:hidden;">
                                    <div style="height:100%; border-radius:9999px; background:#34d399; width:{{ $barPct }}%;"></div>
                                </div>
                                @if ($row->revenue > 0)
                                    <p style="font-size:0.7rem; color:#9ca3af; margin:3px 0 0;">{{ $currencySymbol }}{{ number_format($row->revenue, 0) }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

    </div>

</x-filament-panels::page>
