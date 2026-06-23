<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('For Businesses') }} — {{ config('app.name', 'Slotara') }}</title>
    <meta name="description" content="{{ __('Set up your booking page in minutes. Add services, set your schedule, build custom forms, and embed bookings anywhere — no code required.') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="/font-proxy?family=Inter&weights=400;500;600;700;800;900">
</head>
<body class="min-h-screen bg-[#fafafa]">
<div class="page-bg min-h-screen flex flex-col">
    <div class="dot-pattern fixed inset-0 opacity-40 pointer-events-none"></div>

    {{-- ─── Nav ─── --}}
    @include('layouts.partials.front-nav', ['activePage' => 'businesses'])

    {{-- ─── Hero ─── --}}
    <section class="relative z-10 flex flex-col items-center text-center px-6 pt-16 pb-20 sm:pt-24 sm:pb-28">

        <div class="inline-flex items-center gap-2 bg-violet-50 border border-violet-200 text-violet-700 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-8 uppercase tracking-widest">
            <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></span>
            {{ __('For Business Owners') }}
        </div>

        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-gray-900 leading-[1.05] max-w-3xl tracking-tight">
            {{ __('Your booking page,') }}<br>
            <span class="gradient-text">{{ __('ready in minutes.') }}</span>
        </h1>

        <p class="mt-6 text-lg sm:text-xl text-gray-500 max-w-xl leading-relaxed">
            {{ __('Add your services, set your schedule, customise the booking form, and share it anywhere — no code, no complexity.') }}
        </p>

        <div class="mt-10 flex flex-col sm:flex-row items-center gap-3">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold px-7 py-3.5 rounded-xl glow-btn shadow-md">
                {{ __('Create free page') }}
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
            <a href="#features" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold px-7 py-3.5 rounded-xl border border-gray-200 shadow-sm transition-all">
                {{ __('See how it works') }}
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </a>
        </div>

        {{-- Quick wins --}}
        <div class="mt-10 flex flex-wrap justify-center gap-x-8 gap-y-3 text-sm text-gray-500">
            @foreach ([
                [__('Free to get started'), 'text-emerald-500'],
                [__('Live in under 5 minutes'), 'text-violet-500'],
                [__('No code required'), 'text-blue-500'],
                [__('Works on any device'), 'text-orange-500'],
            ] as [$label, $color])
                <span class="flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5 {{ $color }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                    </svg>
                    {{ $label }}
                </span>
            @endforeach
        </div>
    </section>

    {{-- ─── Feature Cards ─── --}}
    <section id="features" class="relative z-10 w-full max-w-6xl mx-auto px-6 pb-24">

        <div class="text-center mb-14">
            <p class="text-xs font-bold text-violet-600 uppercase tracking-widest mb-3">{{ __('Everything included') }}</p>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">{{ __('All the tools you need') }}</h2>
            <p class="mt-3 text-gray-500 max-w-md mx-auto leading-relaxed">{{ __('One platform for your booking page, schedule, staff, and customer flow.') }}</p>
        </div>

        {{-- Grid: first 3 equal, then 2+1 bento, then special embed card --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- 1. List your business --}}
            <div class="feature-card bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col gap-5">
                <div class="w-11 h-11 rounded-xl bg-violet-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">{{ __('List your business') }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ __('Create a branded booking page with your name, logo, and tagline in minutes. Your own shareable URL — ready to send.') }}</p>
                </div>
                {{-- Mini mockup --}}
                <div class="mt-auto rounded-xl bg-gray-50 border border-gray-100 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-violet-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">A</div>
                        <div>
                            <div class="text-xs font-semibold text-gray-800">Acme Dental</div>
                            <div class="text-[10px] text-gray-400">yourapp.com/acme-dental</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach (['Cleaning', 'Whitening', 'Braces', '+ 4 more'] as $s)
                        <span class="text-[10px] font-medium bg-white border border-gray-200 rounded-full px-2 py-0.5 text-gray-600">{{ $s }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 2. Services & providers --}}
            <div class="feature-card bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col gap-5">
                <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">{{ __('Services & providers') }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ __('Add unlimited services with names, durations, and prices. Assign staff members — customers can choose who they book with.') }}</p>
                </div>
                {{-- Mini mockup --}}
                <div class="mt-auto space-y-2">
                    @foreach ([
                        ['Haircut', '30 min', '$45', ['S', 'J']],
                        ['Colour', '90 min', '$120', ['S']],
                        ['Blowdry', '25 min', '$35', ['S', 'J', 'M']],
                    ] as [$name, $dur, $price, $staff])
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 border border-gray-100 px-3 py-2">
                        <div>
                            <div class="text-xs font-semibold text-gray-800">{{ $name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $dur }} · {{ $price }}</div>
                        </div>
                        <div class="flex -space-x-1">
                            @foreach ($staff as $i => $initial)
                            <div class="w-5 h-5 rounded-full bg-indigo-{{ $i === 0 ? '500' : ($i === 1 ? '400' : '300') }} border border-white text-white text-[8px] font-bold flex items-center justify-center">{{ $initial }}</div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 3. Set schedule --}}
            <div class="feature-card bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col gap-5">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">{{ __('Set your schedule') }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ __('Define weekly availability for each provider. Block holidays, set buffer time between bookings, and control how far ahead customers can book.') }}</p>
                </div>
                {{-- Mini availability grid --}}
                <div class="mt-auto space-y-1.5">
                    @foreach ([
                        ['Mon', '9:00 – 18:00', true],
                        ['Tue', '9:00 – 18:00', true],
                        ['Wed', 'Closed', false],
                        ['Thu', '10:00 – 17:00', true],
                        ['Fri', '9:00 – 15:00', true],
                    ] as [$day, $hours, $open])
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="font-semibold text-gray-500 w-8">{{ $day }}</span>
                        <div class="flex-1 mx-2">
                            @if ($open)
                                <div class="h-1.5 rounded-full bg-blue-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-blue-500" style="width:{{ $day === 'Thu' ? '58%' : ($day === 'Fri' ? '48%' : '72%') }}"></div>
                                </div>
                            @else
                                <div class="h-1.5 rounded-full bg-gray-100"></div>
                            @endif
                        </div>
                        <span class="{{ $open ? 'text-gray-600' : 'text-gray-300 italic' }} font-medium min-w-[80px] text-right">{{ $hours }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 4. Form builder (2 cols wide on lg) --}}
            <div class="feature-card bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col gap-5 lg:col-span-2">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 mb-1.5">{{ __('Drag-and-drop form builder') }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed max-w-md">{{ __('Collect exactly the information you need. Add text fields, dropdowns, checkboxes, and more. Mark fields as required or optional — and reorder them with a drag.') }}</p>
                    </div>
                </div>
                {{-- Mini form mockup (2 col layout since card is wide) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-auto">
                    @foreach ([
                        ['Full name', 'Text field', 'text-purple-600 bg-purple-50', true],
                        ['Email address', 'Email', 'text-blue-600 bg-blue-50', true],
                        ['Phone number', 'Tel field', 'text-indigo-600 bg-indigo-50', false],
                        ['Preferred date', 'Date picker', 'text-violet-600 bg-violet-50', false],
                        ['How did you hear about us?', 'Dropdown', 'text-pink-600 bg-pink-50', false],
                        ['Special notes', 'Textarea', 'text-gray-600 bg-gray-50', false],
                    ] as [$label, $type, $badgeClass, $required])
                    <div class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50/60 px-3 py-2.5 group">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0 cursor-grab" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/></svg>
                            <span class="text-xs font-medium text-gray-700 truncate">{{ $label }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                            <span class="text-[9px] font-semibold {{ $badgeClass }} px-1.5 py-0.5 rounded-full">{{ $type }}</span>
                            @if ($required)
                            <span class="text-[9px] font-bold text-red-400">*</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <button class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-600 bg-purple-50 hover:bg-purple-100 border border-purple-100 rounded-lg px-3 py-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        {{ __('Add field') }}
                    </button>
                    <span class="text-xs text-gray-400">{{ __('Text · Email · Phone · Dropdown · Checkbox · Date · Textarea') }}</span>
                </div>
            </div>

            {{-- 5. Themes --}}
            <div class="feature-card bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col gap-5">
                <div class="w-11 h-11 rounded-xl bg-pink-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">{{ __('Beautiful themes') }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ __('Choose from professionally designed themes. Set your brand colour, pick a font, and make the booking experience feel like yours.') }}</p>
                </div>
                {{-- Theme picker mockup --}}
                <div class="mt-auto space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ([
                            ['Classic', 'bg-white border-2 border-violet-500', 'text-gray-900'],
                            ['Lumina', 'bg-gray-900 border-2 border-transparent', 'text-white'],
                        ] as [$name, $bg, $text])
                        <div class="{{ $bg }} rounded-xl p-3 cursor-pointer">
                            <div class="space-y-1.5 mb-2">
                                <div class="h-1.5 rounded-full {{ $name === 'Classic' ? 'bg-gray-200' : 'bg-gray-700' }} w-3/4"></div>
                                <div class="h-1.5 rounded-full {{ $name === 'Classic' ? 'bg-gray-100' : 'bg-gray-600' }} w-1/2"></div>
                                <div class="h-4 rounded-lg bg-violet-500 w-full mt-2"></div>
                            </div>
                            <div class="text-[9px] font-semibold {{ $text }}">{{ $name }}</div>
                        </div>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-medium text-gray-500">{{ __('Brand colour:') }}</span>
                        <div class="flex gap-1.5">
                            @foreach (['bg-violet-500', 'bg-blue-500', 'bg-emerald-500', 'bg-rose-500', 'bg-amber-500'] as $i => $c)
                            <div class="w-4 h-4 rounded-full {{ $c }} {{ $i === 0 ? 'ring-2 ring-offset-1 ring-violet-500' : '' }}"></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ─── Embed Section (Hero feature) ─── --}}
    <section class="relative z-10 w-full embed-bg overflow-hidden">
        <div class="embed-dot absolute inset-0"></div>
        {{-- Decorative orbs --}}
        <div class="absolute -top-20 -right-20 w-80 h-80 rounded-full bg-violet-600/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-64 h-64 rounded-full bg-indigo-600/10 blur-3xl pointer-events-none"></div>

        <div class="relative max-w-6xl mx-auto px-6 py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

                {{-- Left: text + options --}}
                <div>
                    <div class="inline-flex items-center gap-2 bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-6 uppercase tracking-widest">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 0 1 1 1v1h1a1 1 0 0 1 0 2H6v1a1 1 0 0 1-2 0V6H3a1 1 0 0 1 0-2h1V3a1 1 0 0 1 1-1Zm0 10a1 1 0 0 1 1 1v1h1a1 1 0 0 1 0 2H6v1a1 1 0 0 1-2 0v-1H3a1 1 0 0 1 0-2h1v-1a1 1 0 0 1 1-1ZM12 2a1 1 0 0 1 .967.744L14.146 7.2 17.5 9.134a1 1 0 0 1 0 1.732l-3.354 1.935-1.18 4.455a1 1 0 0 1-1.933 0L9.854 12.8 6.5 10.866a1 1 0 0 1 0-1.732l3.354-1.935 1.18-4.455A1 1 0 0 1 12 2Z" clip-rule="evenodd"/></svg>
                        {{ __('Share & embed anywhere') }}
                    </div>

                    <h2 class="text-3xl sm:text-4xl font-black text-white leading-tight mb-4">
                        {{ __('Works on your') }}<br>
                        <span style="background: linear-gradient(90deg, #a5f3fc 0%, #818cf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ __('existing website') }}</span>
                    </h2>
                    <p class="text-white/60 leading-relaxed mb-10">
                        {{ __('No need to move your whole site. Drop your booking form into any site. Three integration options, all free.') }}
                    </p>

                    <div class="space-y-4">

                        {{-- Option 1: Direct link --}}
                        <div class="rounded-2xl bg-white/6 border border-white/10 p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-white">{{ __('Direct link') }}</div>
                                    <div class="text-xs text-white/40">{{ __('Share on social, email, or anywhere') }}</div>
                                </div>
                            </div>
                            <div class="code-block text-[12px]">
                                <span class="text-white/40">https://</span><span class="str-token">yourapp.com</span><span class="text-white/60">/acme-dental</span>
                            </div>
                        </div>

                        {{-- Option 2: Popup embed --}}
                        <div class="rounded-2xl bg-white/6 border border-white/10 p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/20 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.25V18a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18V8.25m-18 0V6a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 6v2.25m-18 0h18M5.25 6h.008v.008H5.25V6ZM7.5 6h.008v.008H7.5V6Zm2.25 0h.008v.008H9.75V6Z"/></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-white">{{ __('Popup widget') }} <span class="ml-1 text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/20 rounded-full px-2 py-0.5">{{ __('Recommended') }}</span></div>
                                    <div class="text-xs text-white/40">{{ __('Adds a "Book Now" button to your site') }}</div>
                                </div>
                            </div>
                            <div class="code-block">
<span class="cmt-token">&lt;!-- Paste before &lt;/body&gt; --&gt;</span>
<span class="tag-token">&lt;script</span> <span class="attr-token">src</span>=<span class="str-token">"https://yourapp.com/embed.js"</span>
        <span class="attr-token">data-business</span>=<span class="str-token">"acme-dental"</span>
        <span class="attr-token">data-mode</span>=<span class="str-token">"popup"</span>
        <span class="attr-token">data-label</span>=<span class="str-token">"{{ __('Book an appointment') }}"</span><span class="tag-token">&gt;&lt;/script&gt;</span>
                            </div>
                        </div>

                        {{-- Option 3: Inline iframe --}}
                        <div class="rounded-2xl bg-white/6 border border-white/10 p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-white">{{ __('Inline embed') }}</div>
                                    <div class="text-xs text-white/40">{{ __('Full booking widget inside your page') }}</div>

                                </div>
                            </div>
                            <div class="code-block">
<span class="tag-token">&lt;iframe</span>
  <span class="attr-token">src</span>=<span class="str-token">"https://yourapp.com/acme-dental?embed=inline"</span>
  <span class="attr-token">width</span>=<span class="str-token">"100%"</span> <span class="attr-token">height</span>=<span class="str-token">"650"</span>
  <span class="attr-token">frameborder</span>=<span class="str-token">"0"</span><span class="tag-token">&gt;&lt;/iframe&gt;</span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Right: website mockup --}}
                <div class="hidden lg:block">
                    <div class="relative">
                        {{-- Browser chrome --}}
                        <div class="rounded-2xl overflow-hidden shadow-2xl shadow-black/40 border border-white/10">
                            {{-- Browser bar --}}
                            <div class="bg-[#1a1635] px-4 py-3 flex items-center gap-3 border-b border-white/5">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-400/60"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-400/60"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-400/60"></div>
                                </div>
                                <div class="flex-1 bg-white/8 rounded-full px-3 py-1 text-[10px] text-white/30 font-mono">acme-dental.com/services</div>
                            </div>
                            {{-- "Their" website --}}
                            <div class="bg-[#f8f7f5] p-5">
                                {{-- Fake site header --}}
                                <div class="flex items-center justify-between mb-5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-teal-500"></div>
                                        <span class="text-xs font-bold text-gray-800">Acme Dental</span>
                                    </div>
                                    <div class="flex gap-3">
                                        @foreach (['About', 'Services', 'Contact'] as $link)
                                        <span class="text-[9px] text-gray-400">{{ $link }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                {{-- Fake hero text --}}
                                <div class="mb-5">
                                    <div class="h-3 bg-gray-800 rounded w-2/3 mb-2"></div>
                                    <div class="h-2 bg-gray-200 rounded w-full mb-1.5"></div>
                                    <div class="h-2 bg-gray-200 rounded w-5/6"></div>
                                </div>
                                {{-- Embedded booking widget --}}
                                <div class="rounded-xl border-2 border-dashed border-violet-300 bg-white shadow-md overflow-hidden">
                                    <div class="bg-violet-600 px-4 py-2.5 flex items-center gap-2">
                                        <div class="w-4 h-3 flex flex-col justify-between">
                                            <div class="h-0.5 bg-white/70 rounded"></div>
                                            <div class="h-px bg-white/40 rounded w-3/4"></div>
                                            <div class="h-px bg-white/40 rounded"></div>
                                        </div>
                                        <span class="text-[10px] font-semibold text-white">{{ __('Book an appointment') }}</span>
                                    </div>
                                    <div class="p-4 space-y-2.5">
                                        <div>
                                            <div class="text-[8px] font-semibold text-gray-500 mb-1">{{ __('Select service') }}</div>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                @foreach (['Cleaning ✓', 'Whitening', 'Braces', 'Consultation'] as $i => $s)
                                                <div class="rounded-lg border {{ $i === 0 ? 'border-violet-500 bg-violet-50' : 'border-gray-200 bg-white' }} px-2 py-1.5 text-[8px] font-medium {{ $i === 0 ? 'text-violet-700' : 'text-gray-600' }}">{{ $s }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-[8px] font-semibold text-gray-500 mb-1">{{ __('Pick a time') }}</div>
                                            <div class="grid grid-cols-4 gap-1">
                                                @foreach (['9:00', '10:30', '11:00', '14:00', '15:30', '16:00', '17:00', '17:30'] as $i => $t)
                                                <div class="rounded text-center py-1 text-[7px] font-medium {{ $i === 2 ? 'bg-violet-600 text-white' : 'bg-gray-50 text-gray-600 border border-gray-200' }}">{{ $t }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="bg-violet-600 rounded-lg py-2 text-center text-[9px] font-bold text-white">{{ __('Confirm booking') }} →</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Floating badge --}}
                        <div class="absolute -bottom-4 -right-4 bg-emerald-500 text-white text-[10px] font-bold rounded-xl px-3 py-2 shadow-lg flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                            {{ __('Works on any site') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ─── Final CTA ─── --}}
    <section class="relative z-10 w-full max-w-4xl mx-auto px-6 py-24">
        <div class="relative bg-gradient-to-br from-violet-600 to-indigo-600 rounded-3xl overflow-hidden shadow-2xl shadow-violet-200/60">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 24px 24px;"></div>
            <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-8 -left-8 w-32 h-32 rounded-full bg-white/5"></div>
            <div class="relative px-8 py-12 sm:px-14 text-center">
                <p class="text-xs font-bold text-violet-200 uppercase tracking-widest mb-4">{{ __('Ready to start?') }}</p>
                <h2 class="text-3xl sm:text-4xl font-black text-white mb-4 leading-tight">{{ __('Your booking page is one click away.') }}</h2>
                <p class="text-violet-100/80 max-w-md mx-auto leading-relaxed mb-8">
                    {{ __('Create your free page in minutes. No credit card required.') }}
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white hover:bg-violet-50 text-violet-700 text-sm font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg glow-btn">
                        {{ __('Create free page') }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="{{ route('for-clients') }}" class="text-sm text-violet-200 hover:text-white transition-colors font-medium">
                        {{ __('View customer experience') }} &rarr;
                    </a>
                </div>
                <p class="mt-4 text-xs text-violet-200/60 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                    {{ __('Free forever for small businesses · No credit card required') }}
                </p>
            </div>
        </div>
    </section>

    @include('layouts.partials.front-footer')
</div>
</body>
</html>
