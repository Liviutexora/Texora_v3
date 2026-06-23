@php
    $selectedDate = $selectedDate ?? null;
    $minDate = $minDate ?? now()->format('Y-m-d');
    $statePath = $statePath ?? 'mountedActions.0.data';
    $pickerId = 'appointment-cal-' . \Illuminate\Support\Str::random(6);
    $flatpickrCss = asset('vendor/flatpickr/flatpickr.min.css');
    $flatpickrJs = asset('vendor/flatpickr/flatpickr.min.js');
@endphp

<div
    x-data="{
        statePath: @js($statePath),
        minDate: @js($minDate),
        selectedDate: @js($selectedDate),
        pickerId: @js($pickerId),
        containerId: @js($pickerId . '-container'),
        fp: null,
        loadFlatpickr() {
            return new Promise((resolve) => {
                if (typeof flatpickr !== 'undefined') {
                    resolve();
                    return;
                }
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = @js($flatpickrCss);
                document.head.appendChild(link);
                const script = document.createElement('script');
                script.src = @js($flatpickrJs);
                script.onload = () => resolve();
                document.head.appendChild(script);
            });
        },
        init() {
            this.$nextTick(() => {
                this.loadFlatpickr().then(() => {
                    const input = document.getElementById(this.pickerId);
                    const container = document.getElementById(this.containerId);
                    if (!input || !container || this.fp) return;
                    const self = this;
                    this.fp = flatpickr(input, {
                        inline: true,
                        appendTo: container,
                        dateFormat: 'Y-m-d',
                        minDate: this.minDate,
                        defaultDate: this.selectedDate || null,
                        static: true,
                        onChange(selectedDates, dateStr) {
                            if (dateStr) {
                                $wire.set(self.statePath + '.date', dateStr);
                                $wire.set(self.statePath + '.slot', null);
                            }
                        }
                    });
                });
            });
        }
    }"
    x-init="init()"
    class="w-full max-w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 overflow-hidden"
>
    <div class="p-3 w-full max-w-full">
        <input type="hidden" id="{{ $pickerId }}" />
        <div id="{{ $pickerId }}-container" class="flatpickr-appointment-inline min-h-[280px] w-full max-w-full"></div>
    </div>
</div>

