@extends('installer.layout')

@section('title', __('installer.Installation Complete'))

@section('content')
    <div class="text-center">
        <div class="mb-6">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
        
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('installer.Installation Complete!') }}</h3>
        <p class="text-gray-600 mb-6">
            {{ __('installer.Your application has been successfully installed. You can now log in with your administrator account.') }}
        </p>
        
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <p class="text-sm text-gray-600">
                {{ __('installer.For security reasons, please ensure the following:') }}
            </p>
            <ul class="mt-2 text-sm text-gray-600 text-left list-disc list-inside">
                <li>{{ __('installer.Remove write permissions from sensitive directories') }}</li>
                <li>{{ __('installer.Set your environment to production when ready') }}</li>
                <li>{{ __('installer.Review your security settings') }}</li>
            </ul>
        </div>

        <a href="{{ url('/admin/login') }}"
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            {{ __('installer.Go to Admin Panel →') }}
        </a>
    </div>
@endsection