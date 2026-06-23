<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} — {{ config('app.name', 'Slotara') }}</title>
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="/font-proxy?family=Inter&weights=400;500;600;700;800;900">
</head>
<body class="min-h-screen flex flex-col bg-[#fafafa]">
<div class="page-bg flex-1 flex flex-col">
    <div class="dot-pattern fixed inset-0 opacity-40 pointer-events-none"></div>

    @include('layouts.partials.front-nav', ['activePage' => ''])

    <main class="relative z-10 flex-1 w-full max-w-3xl mx-auto px-6 py-12 sm:py-16">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-400 mb-8">
            <a href="{{ route('home') }}" class="hover:text-gray-600 transition-colors">{{ __('Home') }}</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="text-gray-500">{{ $page->title }}</span>
        </nav>

        {{-- Page header --}}
        <div class="mb-10">
            <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
                {{ $page->title }}
            </h1>
            @if($page->updated_at)
                <p class="mt-2 text-sm text-gray-400">{{ __('Last updated') }} {{ $page->updated_at->format('d F Y') }}</p>
            @endif
        </div>

        {{-- Content --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 sm:p-10">
            @if($page->content)
                <div class="prose max-w-none">
                    {!! $page->content !!}
                </div>
            @else
                <p class="text-gray-400 italic">{{ __('This page has no content yet.') }}</p>
            @endif
        </div>

    </main>

    @include('layouts.partials.front-footer')
</div>
</body>
</html>
