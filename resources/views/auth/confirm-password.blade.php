@extends('layouts.guest')

@section('title', ucwords(str_replace('_', ' ', 'confirm_password')))

@section('content')
    <div>
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">{{ __('Confirm Password') }}</h2>
        
        <p class="text-sm text-gray-600 text-center mb-6">
            {{ __('Please confirm your password before continuing.') }}
        </p>
        
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
            @csrf
            
            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Password') }}
                </label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input id="password" 
                        type="password" 
                        name="password" 
                        class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-300 @enderror"
                        required 
                        autocomplete="current-password">
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Submit Button --}}
            <button type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                {{ __('Confirm') }}
            </button>
        </form>
    </div>
@endsection

@section('footer')
    @if (Route::has('password.request'))
        <p>
            <a href="{{ route('password.request') }}" class="font-medium hover:text-white">
                {{ __('Forgot Your Password') }}
            </a>
        </p>
    @endif
@endsection