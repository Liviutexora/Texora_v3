@extends('layouts.guest')

@section('title', ucwords(str_replace('_', ' ', 'reset_password')))

@section('content')
    <div>
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">{{ __('Reset Password') }}</h2>
        
        <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
            @csrf
            
            {{-- Password Reset Token --}}
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            
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
                        value="{{ old('email', $request->email) }}"
                        class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @enderror"
                        required 
                        autofocus 
                        autocomplete="username">
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('New Password') }}
                </label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input id="password" 
                           type="password" 
                           name="password" 
                           class="input-with-icon block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-300 @enderror"
                           required 
                           autocomplete="new-password">
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Confirm New Password') }}
                </label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input id="password_confirmation" 
                           type="password" 
                           name="password_confirmation" 
                           class="input-with-icon block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           required 
                           autocomplete="new-password">
                </div>
            </div>
            
            {{-- Submit Button --}}
            <button type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                {{ __('Reset Password') }}
            </button>
        </form>
    </div>
@endsection