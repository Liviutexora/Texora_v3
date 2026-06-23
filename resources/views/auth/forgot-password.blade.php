@extends('layouts.guest')

@section('title', __('Forgot Password'))

@section('content')
    @php
        $recaptchaEnabled = app(\App\Services\RecaptchaService::class)->isEnabled();
        $recaptchaSiteKey = \App\Models\Setting::get('google_recaptcha_site_key');
    @endphp
    
    <div>
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">{{ __('Forgot Password') }}</h2>
        
        <p class="text-sm text-gray-600 text-center mb-6">
            {{ __('Enter your email address and we will send you a link to reset your password.') }}
        </p>
        
        {{-- Session Status --}}
        @if (session('status'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('status') }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('password.email') }}" class="space-y-6" id="forgot-password-form">
            @csrf
            @if($recaptchaEnabled && $recaptchaSiteKey)
                <input type="hidden" name="g-recaptcha-response" id="fp-recaptcha-response">
            @endif

            {{-- Email Address --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Email Address') }}
                </label>
                <div class="input-group">
                    <i class="fas fa-envelope input-icon absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @enderror"
                        required
                        autofocus>
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @error('recaptcha')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            {{-- Submit Button --}}
            <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                {{ __('Send Reset Link') }}
            </button>
        </form>
    </div>
@endsection

@section('footer')
    <p>
        {{ __('Remember your password?') }} 
        <a href="{{ route('login') }}" class="font-medium hover:text-white">
            {{ __('Sign In') }}
        </a>
    </p>
@endsection

@if($recaptchaEnabled && $recaptchaSiteKey)
@push('scripts')
<script src="{{ asset('js/vendor/recaptcha-api.js') }}?render={{ $recaptchaSiteKey }}"></script>
<script>
    'use strict';
    grecaptcha.ready(function () {
        document.getElementById('forgot-password-form').addEventListener('submit', function (e) {
            var tokenInput = document.getElementById('fp-recaptcha-response');
            if (tokenInput && tokenInput.value === '') {
                e.preventDefault();
                var form = this;
                grecaptcha.execute('{{ $recaptchaSiteKey }}', {action: 'forgot_password'}).then(function (token) {
                    tokenInput.value = token;
                    form.submit();
                });
            }
        });
    });
</script>
@endpush
@endif