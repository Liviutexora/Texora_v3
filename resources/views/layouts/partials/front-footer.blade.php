{{--
    Shared front-site footer.
    Usage: @include('layouts.partials.front-footer')
--}}
@php
    $footerPages = \App\Models\Page::where('is_enabled', true)
        ->orderBy('sort_order')
        ->orderBy('title')
        ->get(['title', 'slug']);
@endphp

<footer class="relative z-10 border-t border-gray-100 pt-7 pb-8">
    <div class="max-w-6xl mx-auto px-6">

        {{-- Top row: brand + main nav --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <x-brand-logo :mark-only="true" size="sm" />
                @unless (config('app.hide_powered_by'))
                    <span class="text-xs text-gray-400">{{ __('Powered by') }} <span class="font-semibold text-gray-600">{{ config('app.name', 'Texora') }}</span></span>
                @endunless
            </div>
            <nav class="flex items-center gap-1">
                <a href="{{ route('home') }}" class="text-xs text-gray-500 hover:text-gray-900 transition-colors px-2.5 py-1 rounded-md hover:bg-gray-100">{{ __('For Businesses') }}</a>
                <a href="{{ route('for-clients') }}" class="text-xs text-gray-500 hover:text-gray-900 transition-colors px-2.5 py-1 rounded-md hover:bg-gray-100">{{ __('For Clients') }}</a>
                <a href="{{ route('contact') }}" class="text-xs text-gray-500 hover:text-gray-900 transition-colors px-2.5 py-1 rounded-md hover:bg-gray-100">{{ __('Contact') }}</a>
                @guest
                    <span class="w-px h-3 bg-gray-200 mx-1"></span>
                    <a href="{{ route('register') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-700 transition-colors px-2.5 py-1 rounded-md hover:bg-violet-50">{{ __('Get Started') }}</a>
                    <a href="{{ route('login') }}" class="text-xs text-gray-500 hover:text-gray-900 transition-colors px-2.5 py-1 rounded-md hover:bg-gray-100">{{ __('Sign In') }}</a>
                @endguest
            </nav>
        </div>

        {{-- Legal pages row (only if any exist) --}}
        @if($footerPages->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap items-center justify-center gap-x-1 gap-y-1">
            @foreach($footerPages as $fp)
                @unless($loop->first)
                    <span class="text-gray-200 text-xs select-none">·</span>
                @endunless
                <a href="{{ route('page.show', $fp->slug) }}"
                   class="text-xs text-gray-400 hover:text-gray-600 transition-colors px-1.5 py-0.5 rounded hover:bg-gray-100">
                    {{ $fp->title }}
                </a>
            @endforeach
        </div>
        @endif

    </div>
</footer>
