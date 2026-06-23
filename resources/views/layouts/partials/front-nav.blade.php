{{--
    Shared front-site nav. Include with:
    @include('layouts.partials.front-nav', ['activePage' => 'businesses|clients|contact'])
--}}
@php $activePage ??= ''; @endphp

<header class="relative z-50 w-full px-6 py-5 flex items-center justify-between max-w-6xl mx-auto">
    <x-brand-logo href="{{ route('home') }}" />

    <div class="flex items-center gap-1">
        <a href="{{ route('home') }}"
           class="text-sm px-3 py-1.5 rounded-lg transition-colors
                  {{ $activePage === 'businesses'
                       ? 'font-semibold text-violet-600 bg-violet-50'
                       : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
            {{ __('For Businesses') }}
        </a>
        <a href="{{ route('for-clients') }}"
           class="text-sm px-3 py-1.5 rounded-lg transition-colors
                  {{ $activePage === 'clients'
                       ? 'font-semibold text-violet-600 bg-violet-50'
                       : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
            {{ __('For Clients') }}
        </a>
        <a href="{{ route('contact') }}"
           class="text-sm px-3 py-1.5 rounded-lg transition-colors
                  {{ $activePage === 'contact'
                       ? 'font-semibold text-violet-600 bg-violet-50'
                       : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
            {{ __('Contact') }}
        </a>

        <div class="w-px h-4 bg-gray-200 mx-1"></div>

        {{-- Language Switcher --}}
        @php
            $allLocaleLabels = ['en' => 'EN', 'ro' => 'RO', 'es' => 'ES', 'de' => 'DE', 'fr' => 'FR', 'ar' => 'AR', 'ru' => 'RU', 'zh' => 'ZH', 'hi' => 'HI'];
            $localeLabels = array_intersect_key($allLocaleLabels, array_flip(\App\Http\Middleware\SetLocale::enabledLocales()));
            $languageNames = ['EN' => __('English'), 'RO' => __('Romanian'), 'ES' => __('Spanish'), 'DE' => __('German'), 'FR' => __('French'), 'AR' => __('Arabic'), 'RU' => __('Russian'), 'ZH' => __('Chinese'), 'HI' => __('Hindi')];
            $currentLocale = app()->getLocale();
        @endphp
        <div class="relative" id="sl-lang-menu">
            <button id="sl-lang-btn" type="button"
                    class="flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-900 px-2 py-1.5 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none cursor-pointer"
                    title="{{ __('Select Language') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                </svg>
                {{ strtoupper($currentLocale) }}
            </button>
            <div id="sl-lang-dropdown" class="hidden absolute right-0 mt-1 w-32 bg-white rounded-lg border border-gray-200 shadow-lg py-1 z-50">
                @foreach ($localeLabels as $locale => $label)
                    <form method="POST" action="{{ route('locale.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $locale }}">
                        <button type="submit"
                                class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors
                                       {{ $locale === $currentLocale
                                          ? 'text-violet-600 bg-violet-50'
                                          : 'text-gray-700 hover:bg-gray-50' }}">
                            {{ $label }} — {{ $languageNames[$localeLabels[$locale]] ?? $localeLabels[$locale] }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <div class="w-px h-4 bg-gray-200 mx-1"></div>

        @auth
            @php
                $u = auth()->user();
                if ($u->hasRole('super_admin')) {
                    $dashUrl   = '/admin';
                    $dashLabel = __('Dashboard');
                    $dashIcon  = 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z';
                } elseif ($u->hasRole('tenant_owner')) {
                    $dashUrl   = '/manage';
                    $dashLabel = __('Dashboard');
                    $dashIcon  = 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z';
                } else {
                    $dashUrl   = route('my-bookings');
                    $dashLabel = __('My Bookings');
                    $dashIcon  = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
                }
                // Generate initials avatar
                $nameWords = explode(' ', trim($u->name ?? ''));
                $initials  = strtoupper(($nameWords[0][0] ?? '') . ($nameWords[1][0] ?? ''));
                if (! $initials) $initials = strtoupper(substr($u->email, 0, 2));
            @endphp

            {{-- User dropdown (pure vanilla JS, no Alpine required) --}}
            <div id="sl-user-menu" class="relative ml-1">
                <button id="sl-user-btn"
                        class="flex items-center gap-2 pl-1 pr-2.5 py-1 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none cursor-pointer"
                        type="button">
                    {{-- Avatar circle --}}
                    <span class="w-7 h-7 rounded-full bg-violet-600 flex items-center justify-center text-white text-xs font-bold select-none flex-shrink-0">
                        {{ $initials }}
                    </span>
                    <span class="text-sm font-medium text-gray-700 max-w-[100px] truncate hidden sm:block">
                        {{ $u->name ?? $u->email }}
                    </span>
                    {{-- Chevron --}}
                    <svg class="sl-chevron w-3.5 h-3.5 text-gray-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown panel --}}
                <div class="sl-dropdown absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">

                    {{-- User info --}}
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $u->name ?? __('User') }}</p>
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $u->email }}</p>
                    </div>

                    {{-- Dashboard / My Bookings --}}
                    <a href="{{ $dashUrl }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $dashIcon }}"/>
                        </svg>
                        {{ $dashLabel }}
                    </a>

                    <div class="my-1 border-t border-gray-100"></div>

                    {{-- Sign out --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left cursor-pointer">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                            </svg>
                            {{ __('Sign out') }}
                        </button>
                    </form>
                </div>
            </div>

        @else
            <a href="{{ route('login') }}"
               class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors px-3 py-1.5">
                {{ __('Sign In') }}
            </a>
            <a href="{{ route('register') }}"
               class="text-sm font-semibold bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg transition-all shadow-sm glow-btn ml-1">
                {{ __('Get Started') }}
            </a>
        @endauth
    </div>
</header>
@vite('resources/js/front-nav.js')
