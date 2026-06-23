@extends('layouts.guest')

@section('title', ucwords(str_replace('_', ' ', 'email_verification')))

@section('content')
    <div>
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">{{ __('Verify Your Email') }}</h2>

        <div class="text-center">
            <i class="fas fa-envelope-open-text text-6xl text-blue-600 mb-4"></i>

            <p class="text-sm text-gray-600 mb-6">
                {{ __('Thanks for signing up! Please verify your email address to continue.') }}
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ __('Verification Link Sent') }}
                </div>
            @endif

            @if ($errors->has('email'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $errors->first('email') }}
                </div>
            @endif

            <div class="space-y-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        {{ __('Resend Verification Email') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        {{ __('Sign Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection