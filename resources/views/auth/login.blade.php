@extends('layouts.guest')

@section('title', __('Sign In'))

@section('content')
    <div>
        <div class="mb-8">
            <h2 class="text-2xl font-black text-gray-900">{{ __('Welcome back') }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ __('Sign in to your account to continue') }}</p>
        </div>

        @if(config('demo.enabled'))
        <div class="mb-6 rounded-xl border border-violet-200 overflow-hidden">
            <div class="bg-gradient-to-r from-violet-600 to-indigo-600 px-4 py-2.5 flex items-center gap-2">
                <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-white text-xs font-semibold tracking-wide">{{ __('Demo — click any account to log in instantly') }}</span>
            </div>
            @foreach ([
                [
                    'role'  => __('Super Admin'),
                    'email' => 'admin@slotara.app',
                    'sub'   => __('Full platform access'),
                    'panel' => '/admin',
                    'cls'   => 'bg-red-100 text-red-700',
                    'icon'  => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                ],
                [
                    'role'  => __('Tenant Owner'),
                    'email' => 'owner@velvet-chair.demo',
                    'sub'   => 'Velvet Chair Studio (Salon)',
                    'panel' => '/manage',
                    'cls'   => 'bg-violet-100 text-violet-700',
                    'icon'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 00-1-1h-2a1 1 0 00-1 1v5m4 0H9',
                ],
                [
                    'role'  => __('Staff'),
                    'email' => 'staff@slotara.app',
                    'sub'   => 'Sofia Reyes — Velvet Chair Salon',
                    'panel' => '/manage',
                    'cls'   => 'bg-blue-100 text-blue-700',
                    'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                ],
                [
                    'role'  => __('Client'),
                    'email' => 'client@slotara.app',
                    'sub'   => __('View & manage bookings'),
                    'panel' => '/my-bookings',
                    'cls'   => 'bg-emerald-100 text-emerald-700',
                    'icon'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                ],
            ] as $account)
            <button type="button"
                onclick="document.getElementById('email').value='{{ $account['email'] }}';document.getElementById('password').value='password';"
                class="w-full flex items-center justify-between px-4 py-3 bg-white hover:bg-violet-50 border-b border-gray-100 last:border-b-0 text-left transition-colors group">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full {{ $account['cls'] }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $account['icon'] }}"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $account['role'] }}</p>
                        <p class="text-xs text-gray-400">{{ $account['sub'] }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $account['email'] }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0 ml-2">
                    <span class="text-xs text-gray-400 font-mono bg-gray-100 group-hover:bg-violet-100 px-2 py-1 rounded transition-colors block">{{ __('password') }}</span>
                    <span class="text-xs text-gray-300 mt-1 block">→ {{ $account['panel'] }}</span>
                </div>
            </button>
            @endforeach
        </div>
        @endif

        @if (session('status'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        @php
        $action = str_contains(url()->current(), 'admin')
            ? route('admin.login')
            : route('login');
        $recaptchaEnabled = app(\App\Services\RecaptchaService::class)->isEnabled();
        $recaptchaSiteKey = \App\Models\Setting::get('google_recaptcha_site_key');
        @endphp

        <form method="POST" action="{{ $action }}" class="space-y-5" id="login-form">
            @csrf
            @if($recaptchaEnabled && $recaptchaSiteKey)
                <input type="hidden" name="g-recaptcha-response" id="login-recaptcha-response">
            @endif

            <div>
                <label for="email" class="auth-label">{{ __('Email Address') }}</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        class="auth-input @error('email') error @enderror"
                        required autofocus autocomplete="username" placeholder="{{ __('you@example.com') }}">
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="auth-label">{{ __('Password') }}</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                        </svg>
                    </span>
                    <input id="password" type="password" name="password"
                        class="auth-input @error('password') error @enderror"
                        required autocomplete="current-password" placeholder="••••••••">
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="w-4 h-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500 cursor-pointer">
                    <span class="text-sm text-gray-600">{{ __('Remember Me') }}</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-violet-600 hover:text-violet-700 font-medium">
                        {{ __('Forgot Password') }}?
                    </a>
                @endif
            </div>

            <button type="submit" class="auth-submit mt-2">
                {{ __('Sign In') }}
            </button>

            @error('recaptcha')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror

        </form>
    </div>
@endsection

@section('footer')
    @if (Route::has('register') && \App\Models\Setting::get('allow_registration', true))
        {{ __("Don't have an account?") }}
        <a href="{{ route('register') }}" class="font-semibold text-violet-600 hover:text-violet-700 ml-1">
            {{ __('Sign Up') }}
        </a>
    @endif
@endsection

@if($recaptchaEnabled && $recaptchaSiteKey)
@push('scripts')
<script src="{{ asset('js/vendor/recaptcha-api.js') }}?render={{ $recaptchaSiteKey }}"></script>
<script>
    'use strict';
    grecaptcha.ready(function () {
        document.getElementById('login-form').addEventListener('submit', function (e) {
            var tokenInput = document.getElementById('login-recaptcha-response');
            if (tokenInput && tokenInput.value === '') {
                e.preventDefault();
                var form = this;
                grecaptcha.execute('{{ $recaptchaSiteKey }}', {action: 'login'}).then(function (token) {
                    tokenInput.value = token;
                    form.submit();
                });
            }
        });
    });
</script>
@endpush
@endif
