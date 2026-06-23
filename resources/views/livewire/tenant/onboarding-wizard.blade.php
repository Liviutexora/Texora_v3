<div class="max-w-xl mx-auto">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Set up your booking page') }}</h1>
        <p class="text-gray-500 mt-1">{{ __('Takes about 2 minutes. You can change everything later.') }}</p>
    </div>

    {{-- Progress --}}
    @if ($step < 5)
    <div class="flex items-center gap-1 mb-8 justify-center">
        @foreach ([__('Business'), __('Service'), __('Provider'), __('Hours')] as $i => $label)
            @php $n = $i + 1; @endphp
            <div class="flex items-center gap-1">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                    {{ $step > $n ? 'bg-violet-600 text-white' : ($step === $n ? 'bg-violet-700 text-white' : 'bg-gray-200 text-gray-500') }}">
                    {{ $step > $n ? '✓' : $n }}
                </div>
                <span class="text-xs hidden sm:block {{ $step === $n ? 'text-violet-700 font-semibold' : 'text-gray-400' }}">{{ $label }}</span>
                @if ($i < 3)<div class="w-6 h-px bg-gray-200 mx-1"></div>@endif
            </div>
        @endforeach
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">

        {{-- ── Step 1: Business Profile ──────────────────────────────────── --}}
        @if ($step === 1)
        <h2 class="text-lg font-semibold text-gray-800">{{ __('Your Business') }}</h2>

        <div>
            <label class="text-sm font-medium text-gray-700">{{ __('Business Name') }} <span class="text-red-500">*</span></label>
            <input type="text" wire:model.live="businessName" placeholder="{{ __("Priya's Beauty Studio") }}"
                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            @error('businessName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700">{{ __('Booking URL') }}</label>
            <div class="mt-1 flex items-center rounded-lg border overflow-hidden focus-within:ring-2 focus-within:ring-violet-500 {{ $errors->has('slug') ? 'border-red-400' : ($slug ? 'border-green-400' : 'border-gray-300') }}">
                <span class="px-3 py-2 bg-gray-50 text-gray-400 text-sm border-r {{ $errors->has('slug') ? 'border-red-400' : ($slug ? 'border-green-400' : 'border-gray-300') }} whitespace-nowrap">{{ url('/') }}/</span>
                <input type="text" wire:model.lazy="slug" placeholder="{{ __('priyas-beauty') }}"
                    class="flex-1 px-3 py-2 text-sm focus:outline-none">
                @if ($slug && ! $errors->has('slug'))
                    <span class="px-3 text-green-500" title="{{ __('Slug available') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                @elseif ($errors->has('slug'))
                    <span class="px-3 text-red-400" title="{{ __('Slug unavailable') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </span>
                @endif
            </div>
            @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700">{{ __('Tagline') }} <span class="text-gray-400">({{ __('optional') }})</span></label>
            <input type="text" wire:model="tagline" placeholder="{{ __('e.g. Premium cuts, zero wait time.') }}"
                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
        </div>


        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

            {{-- ── Timezone searchable combobox ──────────────────────────────── --}}
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">
                    {{ __('Timezone') }}
                </label>
                <div class="lc-wrap"
                     x-data="{
                         open: false,
                         search: '',
                         val: @entangle('timezone'),
                         opts: @js($timezoneOptions),
                         get filtered() {
                             const q = (this.search||'').toLowerCase().trim();
                             if (!q) return this.opts;
                             return this.opts.filter(o => o.toLowerCase().includes(q));
                         },
                         init()   { this.search = this.val || ''; },
                         pick(tz) { this.val = tz; this.search = tz; this.open = false; },
                         onFocus(){ this.open = true; this.search = ''; },
                         onBlur() { setTimeout(() => { this.open = false; this.search = this.val || ''; }, 160); }
                     }">

                    <div class="lc-trigger">
                        <input type="text"
                               class="lc-input"
                               x-model="search"
                               @focus="onFocus"
                               @blur="onBlur"
                               placeholder="{{ __('Search timezone…') }}">
                        <span class="lc-icon">
                            {{-- chevron when closed, magnifier when open --}}
                            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            <svg x-show="open"  x-cloak xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                    </div>

                    <div x-show="open" x-cloak class="lc-panel"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        <div class="lc-hint" x-text="search ? filtered.length + ' result' + (filtered.length===1?'':'s') : opts.length + ' timezones'"></div>
                        <div class="lc-list">
                            <template x-for="tz in filtered.slice(0, 300)" :key="tz">
                                <div @mousedown.prevent="pick(tz)"
                                     class="lc-opt"
                                     :class="val === tz ? 'lc-sel' : ''">
                                    <span x-show="val === tz" class="lc-check">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                                    </span>
                                    <span x-show="val !== tz" class="lc-check-empty"></span>
                                    <span x-text="tz"></span>
                                </div>
                            </template>
                            <div x-show="filtered.length === 0" class="lc-none">{{ __('No timezone found') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Currency searchable combobox ────────────────────────────────── --}}
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">
                    {{ __('Currency') }}
                </label>
                <div class="lc-wrap"
                     x-data="{
                         open: false,
                         search: '',
                         val: @entangle('currency'),
                         opts: @js($currencyOptions),
                         get filtered() {
                             const q = (this.search||'').toLowerCase().trim();
                             if (!q) return this.opts;
                             return this.opts.filter(o =>
                                 o.label.toLowerCase().includes(q) ||
                                 o.code.toLowerCase().includes(q)
                             );
                         },
                         labelOf(code) {
                             const o = this.opts.find(x => x.code === code);
                             return o ? o.label : code;
                         },
                         init()     { this.search = this.labelOf(this.val); },
                         pick(opt)  { this.val = opt.code; this.search = opt.label; this.open = false; },
                         onFocus()  { this.open = true; this.search = ''; },
                         onBlur()   { setTimeout(() => { this.open = false; this.search = this.labelOf(this.val); }, 160); }
                     }">

                    <div class="lc-trigger">
                        <input type="text"
                               class="lc-input"
                               x-model="search"
                               @focus="onFocus"
                               @blur="onBlur"
                               placeholder="{{ __('Search currency…') }}">
                        <span class="lc-icon">
                            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            <svg x-show="open"  x-cloak xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                    </div>

                    <div x-show="open" x-cloak class="lc-panel"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        <div class="lc-hint" x-text="search ? filtered.length + ' result' + (filtered.length===1?'':'s') : opts.length + ' currencies'"></div>
                        <div class="lc-list">
                            <template x-for="opt in filtered" :key="opt.code">
                                <div @mousedown.prevent="pick(opt)"
                                     class="lc-opt"
                                     :class="val === opt.code ? 'lc-sel' : ''">
                                    <span x-show="val === opt.code" class="lc-check">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                                    </span>
                                    <span x-show="val !== opt.code" class="lc-check-empty"></span>
                                    <span x-text="opt.label"></span>
                                </div>
                            </template>
                            <div x-show="filtered.length === 0" class="lc-none">{{ __('No currency found') }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <button wire:click="completeStep1"
            class="w-full bg-violet-600 text-white font-semibold py-2.5 rounded-xl hover:bg-violet-700 transition">
            Continue →
        </button>
        @endif

        {{-- ── Step 2: First Service ──────────────────────────────────────── --}}
        @if ($step === 2)
        <h2 class="text-lg font-semibold text-gray-800">{{ __('Your First Service') }}</h2>
        <p class="text-sm text-gray-500">{{ __('What do clients book with you? (e.g. Haircut, Yoga Class, Consultation)') }}</p>

        <div>
            <label class="text-sm font-medium text-gray-700">{{ __('Service Name') }} <span class="text-red-500">*</span></label>
            <input type="text" wire:model="serviceName" placeholder="{{ __('e.g. Classic Haircut') }}"
                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            @error('serviceName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('Duration (minutes)') }}</label>
                <input type="number" wire:model="serviceDuration" min="5" max="480"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('Price (:currency)', ['currency' => $currency]) }}</label>
                <input type="number" wire:model="servicePrice" min="0" step="0.01"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button wire:click="$set('step', 1)" class="flex-1 border border-gray-300 text-gray-700 font-medium py-2.5 rounded-xl hover:bg-gray-50 transition">{{ __('← Back') }}</button>
            <button wire:click="completeStep2" class="flex-1 bg-violet-600 text-white font-semibold py-2.5 rounded-xl hover:bg-violet-700 transition">{{ __('Continue') }} →</button>
        </div>
        @endif

        {{-- ── Step 3: Provider details ───────────────────────────────────── --}}
        @if ($step === 3)
        <h2 class="text-lg font-semibold text-gray-800">{{ __('Your Role') }}</h2>
        <p class="text-sm text-gray-500">{{ __("You'll be added as the first provider. You can add more staff later.") }}</p>

        <div>
            <label class="text-sm font-medium text-gray-700">{{ __('Your Job Title') }} <span class="text-gray-400">({{ __('optional') }})</span></label>
            <input type="text" wire:model="jobTitle" placeholder="{{ __('e.g. Head Stylist, Lead Trainer') }}"
                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
        </div>

        <div class="flex gap-3 pt-2">
            <button wire:click="$set('step', 2)" class="flex-1 border border-gray-300 text-gray-700 font-medium py-2.5 rounded-xl hover:bg-gray-50 transition">{{ __('← Back') }}</button>
            <button wire:click="completeStep3" class="flex-1 bg-violet-600 text-white font-semibold py-2.5 rounded-xl hover:bg-violet-700 transition">{{ __('Continue') }} →</button>
        </div>
        @endif

        {{-- ── Step 4: Working hours ──────────────────────────────────────── --}}
        @if ($step === 4)
        <h2 class="text-lg font-semibold text-gray-800">{{ __('Working Hours') }}</h2>
        <p class="text-sm text-gray-500">{{ __('Set your default schedule. You can customise per-day later.') }}</p>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('Start Time') }}</label>
                <input type="time" wire:model="shiftStart"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('Number of Slots') }}</label>
                <input type="number" wire:model="shiftSlots" min="1" max="50"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 block mb-2">{{ __('Available Days') }}</label>
            @error('shiftDays') <p class="text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
            <div class="flex flex-wrap gap-2">
                @foreach ([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'] as $num => $label)
                <label class="cursor-pointer">
                    <input type="checkbox" wire:model="shiftDays" value="{{ $num }}" class="sr-only peer">
                    <span class="inline-block px-3 py-1.5 rounded-lg border text-sm font-medium transition
                        peer-checked:bg-violet-600 peer-checked:text-white peer-checked:border-violet-600
                        border-gray-300 text-gray-600 hover:border-violet-400">
                        {{ $label }}
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button wire:click="$set('step', 3)" class="flex-1 border border-gray-300 text-gray-700 font-medium py-2.5 rounded-xl hover:bg-gray-50 transition">{{ __('← Back') }}</button>
            <button wire:click="finish" wire:loading.attr="disabled"
                class="flex-1 bg-violet-600 text-white font-semibold py-2.5 rounded-xl hover:bg-violet-700 transition disabled:opacity-60">
                <span wire:loading.remove>{{ __('Finish Setup') }}</span>
                <span wire:loading>{{ __('Setting up…') }}</span>
            </button>
        </div>
        @endif

        {{-- ── Step 5: Done ───────────────────────────────────────────────── --}}
        @if ($step === 5)
        <div class="text-center py-4">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-1">{{ __("You're all set!") }}</h2>
            <p class="text-gray-500 text-sm mb-6">{{ __('Your booking page is live. Share the link with your clients.') }}</p>

            <div class="bg-violet-50 rounded-xl px-4 py-3 mb-6 flex items-center justify-between gap-2">
                <span class="text-sm text-violet-800 font-medium truncate">{{ url('/' . $slug) }}</span>
                <button onclick="navigator.clipboard.writeText('{{ url('/' . $slug) }}')"
                    class="text-xs text-violet-600 hover:underline shrink-0">{{ __('Copy') }}</button>
            </div>

            <a href="/manage" class="w-full block bg-violet-600 text-white font-semibold py-2.5 rounded-xl hover:bg-violet-700 transition">
                {{ __('Go to Dashboard') }}
            </a>
        </div>
        @endif

    </div>
</div>
