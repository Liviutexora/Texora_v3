<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('installer.Installer')) - {{ config('app.name') }}</title>
    <script src="{{base_url('/js/tailwindcss.js')}}"></script>
</head>
<body class="bg-gray-100">

    {{-- Language switcher (top-right) --}}
    @php
        $allLocaleLabels = ['en' => 'EN', 'es' => 'ES', 'de' => 'DE', 'fr' => 'FR', 'ar' => 'AR', 'ru' => 'RU', 'zh' => 'ZH', 'hi' => 'HI'];
        $localeLabels    = array_intersect_key($allLocaleLabels, array_flip(\App\Http\Middleware\SetLocale::enabledLocales()));
        $languageNames   = ['EN' => 'English', 'ES' => 'Español', 'DE' => 'Deutsch', 'FR' => 'Français', 'AR' => 'العربية', 'RU' => 'Русский', 'ZH' => '中文', 'HI' => 'हिन्दी'];
        $currentLocale   = app()->getLocale();
    @endphp
    <div class="fixed top-4 right-4 z-50">
        <div class="relative" id="inst-lang-menu">
            <button id="inst-lang-btn" type="button"
                    class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-900 bg-white border border-gray-200 shadow-sm px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                </svg>
                {{ strtoupper($currentLocale) }}
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </button>
            <div id="inst-lang-dropdown" class="hidden absolute right-0 mt-1 w-36 bg-white rounded-lg border border-gray-200 shadow-lg py-1 z-50">
                @foreach ($localeLabels as $locale => $label)
                    <form method="POST" action="{{ route('locale.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $locale }}">
                        <button type="submit"
                                class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors
                                       {{ $locale === $currentLocale ? 'text-violet-600 bg-violet-50' : 'text-gray-700 hover:bg-gray-50' }}">
                            {{ $label }} — {{ $languageNames[$label] ?? $label }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    {{ config('app.name') }} {{ __('installer.Installer') }}
                </h2>
                <div class="mt-4">
                    @include('installer.steps')
                </div>
            </div>
            
            <div class="bg-white shadow-xl rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
    <script>
        (function () {
            var btn = document.getElementById('inst-lang-btn');
            var dd  = document.getElementById('inst-lang-dropdown');
            if (!btn || !dd) return;
            btn.addEventListener('click', function (e) { e.stopPropagation(); dd.classList.toggle('hidden'); });
            document.addEventListener('click', function () { dd.classList.add('hidden'); });
        })();
    </script>
</body>
</html>