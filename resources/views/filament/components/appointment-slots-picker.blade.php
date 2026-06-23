@php
    $groupedSlots = $groupedSlots ?? [];
    $selectedSlot = $selectedSlot ?? null;
    $statePath = $statePath ?? 'mountedActions.0.data';
@endphp

<div x-data="{ statePath: @js($statePath), setSlot(v) { $wire.set(this.statePath + '.slot', v); } }" class="w-full">
    @if(empty($groupedSlots))
        <div class="px-8 py-6 text-center bg-gray-50 border border-gray-200 rounded-xl text-gray-500 text-sm">
            No available slots for this date. Try another date.
        </div>
    @else
        <div class="flex flex-col gap-5 w-full">
            @foreach($groupedSlots as $shiftName => $slots)
                <div class="w-full border border-gray-200 rounded-xl bg-white overflow-hidden shadow-sm">
                    {{-- Shift header --}}
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-200 bg-slate-50 font-semibold text-sm text-gray-800">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-100 text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        {{ $shiftName }}
                    </div>
                    {{-- Slot grid --}}
                    <div style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));"
                         class="grid gap-3 p-4">
                        @foreach($slots as $index => $slot)
                            @php
                                $value = $slot['value'];
                                $timeRange = $slot['timeRange'] ?? $slot['value'] ?? '';
                                $isSelected = $selectedSlot === $value;
                            @endphp
                            <button
                                type="button"
                                @click="setSlot(@js($value))"
                                @class([
                                    'flex items-center gap-2 px-4 py-3 rounded-[10px] cursor-pointer text-left min-h-[56px] transition-all duration-150 border-2',
                                    'border-blue-600 bg-blue-50 shadow-[0_2px_8px_rgba(37,99,235,0.2)]' => $isSelected,
                                    'border-gray-200 bg-gray-50 shadow-[0_1px_2px_rgba(0,0,0,0.04)] hover:border-blue-300 hover:bg-blue-50' => !$isSelected,
                                ])
                                @if($isSelected) data-selected="1" @endif
                            >
                                {{-- Radio circle --}}
                                <span @class([
                                    'inline-flex items-center justify-center w-5 h-5 rounded-full border-2 shrink-0',
                                    'border-blue-600 bg-blue-600' => $isSelected,
                                    'border-gray-400 bg-white'   => !$isSelected,
                                ])>
                                    @if($isSelected)
                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                    @endif
                                </span>
                                <span @class([
                                    'text-[13px] font-semibold flex-1 min-w-0 overflow-hidden text-ellipsis',
                                    'text-blue-700' => $isSelected,
                                    'text-gray-800' => !$isSelected,
                                ])>{{ $timeRange }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
