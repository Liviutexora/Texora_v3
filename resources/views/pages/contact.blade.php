<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Contact Us') }} — {{ config('app.name', 'Slotara') }}</title>
    <meta name="description" content="Get in touch with {{ config('app.name', 'Slotara') }}. We're here to help.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
    @php
        $recaptchaEnabled = \App\Models\Setting::get('google_recaptcha_enabled', false);
        $recaptchaSiteKey = \App\Models\Setting::get('google_recaptcha_site_key');
        $email   = \App\Models\Setting::get('contact_email');
        $phone   = \App\Models\Setting::get('contact_phone');
        $address = \App\Models\Setting::get('contact_address');
        $formFields = \App\Http\Controllers\ContactUsController::resolveContactFormFieldsFromSettings();

        // Detect name + email fields so we can pair them side-by-side
        $fieldNames = collect($formFields)->pluck('name')->all();
        $hasNameAndEmail = in_array('name', $fieldNames) && in_array('email', $fieldNames);
    @endphp
    @if($recaptchaEnabled && $recaptchaSiteKey)
        <script src="{{ asset('js/vendor/recaptcha-api.js') }}?render={{ $recaptchaSiteKey }}"></script>
    @endif
    <link rel="stylesheet" href="/font-proxy?family=Inter&weights=400;500;600;700;800;900">
</head>
<body class="min-h-screen flex flex-col bg-[#fafafa]">
<div class="page-bg flex-1 flex flex-col">
    <div class="dot-pattern fixed inset-0 opacity-40 pointer-events-none"></div>

    {{-- Nav --}}
    @include('layouts.partials.front-nav', ['activePage' => 'contact'])

    {{-- Page heading --}}
    <div class="relative z-10 text-center px-6 pt-12 pb-10">
        <div class="inline-flex items-center gap-2 bg-violet-50 border border-violet-200 text-violet-700 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-6 uppercase tracking-widest">
            <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></span>
            {{ __('Get in Touch') }}
        </div>
        <h1 class="text-4xl sm:text-5xl font-black text-gray-900 tracking-tight mb-3">
            {{ __("We'd love to hear from you") }}
        </h1>
        <p class="text-gray-500 text-lg max-w-md mx-auto">{{ __('Have a question? Drop us a message and we will get back to you quickly.') }}</p>
    </div>

    {{-- Two-column layout --}}
    <main class="relative z-10 flex-1 w-full max-w-5xl mx-auto px-6 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.6fr] gap-6 items-start">

            {{-- ─── Left: Info panel ─── --}}
            <div class="info-panel relative rounded-3xl overflow-hidden text-white p-8 lg:sticky lg:top-8">
                <div class="info-dot absolute inset-0 pointer-events-none"></div>
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5"></div>
                <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/5"></div>

                <div class="relative">
                    <h2 class="text-xl font-bold mb-1">{{ __("Let's talk") }}</h2>
                    <p class="text-white/60 text-sm leading-relaxed mb-8">{{ __('We read every message personally.') }}</p>

                    {{-- Contact details --}}
                    <div class="space-y-3 mb-8">
                        @if($email)
                        <a href="mailto:{{ $email }}" class="flex items-center gap-3 group">
                            <div class="w-9 h-9 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center flex-shrink-0 group-hover:bg-white/20 transition-colors">
                                <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-widest">{{ __('Email') }}</p>
                                <p class="text-sm font-semibold text-white group-hover:text-violet-200 transition-colors truncate">{{ $email }}</p>
                            </div>
                        </a>
                        @endif

                        @if($phone)
                        <a href="tel:{{ $phone }}" class="flex items-center gap-3 group">
                            <div class="w-9 h-9 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center flex-shrink-0 group-hover:bg-white/20 transition-colors">
                                <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-widest">{{ __('Phone') }}</p>
                                <p class="text-sm font-semibold text-white group-hover:text-violet-200 transition-colors">{{ $phone }}</p>
                            </div>
                        </a>
                        @endif

                        @if($address)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-widest">{{ __('Address') }}</p>
                                <p class="text-sm font-semibold text-white leading-snug">{{ $address }}</p>
                            </div>
                        </div>
                        @endif

                        @if(!$email && !$phone && !$address)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-widest">{{ __('Email') }}</p>
                                <p class="text-sm font-semibold text-white">{{ __('Use the form to reach us') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-white/10 mb-6"></div>

                    {{-- What to expect --}}
                    <p class="text-xs font-bold text-white/40 uppercase tracking-widest mb-4">{{ __('What to expect') }}</p>
                    <div class="space-y-4">
                        @foreach ([
                            [__('Replies within one business day'), 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                            [__('Every message read by our team'), 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                            [__('Your information is never shared'), 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
                        ] as [$text, $iconPath])
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                                </svg>
                            </div>
                            <span class="text-sm text-white/70 font-medium">{{ $text }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ─── Right: Form ─── --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">

                @if(session('success'))
                <div class="mb-6 flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-4 rounded-xl text-sm font-medium">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-emerald-800 mb-0.5">{{ __('Message sent!') }}</p>
                        <p class="text-emerald-600 font-normal">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-600 px-4 py-4 rounded-xl text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <ul class="space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Inquiry Type --}}
                    <div>
                        <label for="type" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            {{ __('Inquiry type') }} <span class="text-red-400">*</span>
                        </label>
                        <select id="type" name="type" required
                            class="form-input @error('type') error @enderror">
                            <option value="" disabled {{ old('type') ? '' : 'selected' }}>{{ __('Select a type…') }}</option>
                            @foreach(\App\Models\ContactUs::TYPE_LIST as $value => $label)
                                <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pair name + email side-by-side if both exist --}}
                    @php
                        $pairedFields  = [];
                        $remaining     = [];
                        $nameField     = null;
                        $emailField    = null;

                        foreach ($formFields as $f) {
                            if (($f['name'] ?? '') === 'name')  { $nameField  = $f; continue; }
                            if (($f['name'] ?? '') === 'email') { $emailField = $f; continue; }
                            $remaining[] = $f;
                        }
                    @endphp

                    @if($nameField && $emailField)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @foreach([$nameField, $emailField] as $pf)
                        @php
                            $fn = $pf['name']; $lbl = $pf['label'] ?? ucfirst($fn);
                            $req = !empty($pf['required']); $ph = $pf['placeholder'] ?? '';
                        @endphp
                        <div>
                            <label for="{{ $fn }}" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                {{ __($lbl) }}@if($req)<span class="text-red-400 ml-0.5">*</span>@endif
                            </label>
                            <input type="{{ $pf['type'] ?? 'text' }}" id="{{ $fn }}" name="{{ $fn }}"
                                   value="{{ old($fn) }}" placeholder="{{ __($ph) }}"
                                   @if($req) required @endif
                                   class="form-input @error($fn) error @enderror">
                            @error($fn)<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        @endforeach
                    </div>
                    @elseif($nameField)
                        @php $f = $nameField; $fn = $f['name']; $lbl = $f['label'] ?? ucfirst($fn); $req = !empty($f['required']); $ph = $f['placeholder'] ?? ''; @endphp
                        <div>
                            <label for="{{ $fn }}" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __($lbl) }}@if($req)<span class="text-red-400 ml-0.5">*</span>@endif</label>
                            <input type="{{ $f['type'] ?? 'text' }}" id="{{ $fn }}" name="{{ $fn }}" value="{{ old($fn) }}" placeholder="{{ __($ph) }}" @if($req) required @endif class="form-input @error($fn) error @enderror">
                            @error($fn)<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    @elseif($emailField)
                        @php $f = $emailField; $fn = $f['name']; $lbl = $f['label'] ?? ucfirst($fn); $req = !empty($f['required']); $ph = $f['placeholder'] ?? ''; @endphp
                        <div>
                            <label for="{{ $fn }}" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __($lbl) }}@if($req)<span class="text-red-400 ml-0.5">*</span>@endif</label>
                            <input type="{{ $f['type'] ?? 'email' }}" id="{{ $fn }}" name="{{ $fn }}" value="{{ old($fn) }}" placeholder="{{ __($ph) }}" @if($req) required @endif class="form-input @error($fn) error @enderror">
                            @error($fn)<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    {{-- Remaining fields --}}
                    @foreach($remaining as $field)
                        @php
                            $fn  = $field['name'] ?? '';
                            $lbl = $field['label'] ?? ucfirst($fn);
                            $type = $field['type'] ?? 'text';
                            $req  = !empty($field['required']);
                            $ph   = $field['placeholder'] ?? '';
                            $rows = $field['rows'] ?? 4;
                        @endphp
                        @if($fn)
                        <div>
                            <label for="{{ $fn }}" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                {{ __($lbl) }}@if($req)<span class="text-red-400 ml-0.5">*</span>@endif
                            </label>
                            @if($type === 'textarea')
                                <textarea id="{{ $fn }}" name="{{ $fn }}" rows="{{ $rows }}"
                                    placeholder="{{ __($ph) }}" @if($req) required @endif
                                    class="form-input resize-none @error($fn) error @enderror"
                                    style="height: auto;">{{ old($fn) }}</textarea>
                            @else
                                <input type="{{ $type }}" id="{{ $fn }}" name="{{ $fn }}"
                                    value="{{ old($fn) }}" placeholder="{{ __($ph) }}"
                                    @if($req) required @endif
                                    class="form-input @error($fn) error @enderror">
                            @endif
                            @error($fn)<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        @endif
                    @endforeach

                    @if($recaptchaEnabled && $recaptchaSiteKey)
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        @error('recaptcha')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @endif

                    <div class="pt-1">
                        <button type="submit" id="contact-submit-btn"
                            class="w-full py-3.5 px-6 bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold rounded-xl transition-all glow-btn shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                            </svg>
                            {{ __('Send Message') }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </main>

    @include('layouts.partials.front-footer')
</div>

@if($recaptchaEnabled && $recaptchaSiteKey)
<script>
'use strict';
document.getElementById('contact-submit-btn').closest('form').addEventListener('submit', function(e) {
    var tokenInput = document.getElementById('g-recaptcha-response');
    if (!tokenInput) return;
    e.preventDefault();
    var form = this;
    grecaptcha.ready(function() {
        grecaptcha.execute('{{ $recaptchaSiteKey }}', {action: 'contact'}).then(function(token) {
            tokenInput.value = token;
            form.submit();
        });
    });
});
</script>
@endif

</body>
</html>
