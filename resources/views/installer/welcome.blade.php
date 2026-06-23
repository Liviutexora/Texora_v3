@extends('installer.layout')

@section('title', __('installer.Welcome'))

@section('content')
    <div class="text-center">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('installer.Welcome to the Installation Wizard') }}</h3>
        <p class="text-gray-600 mb-6">
            {{ __('installer.This wizard will guide you through the installation process. Please make sure you have the following information ready:') }}
        </p>
        
        <ul class="text-left text-sm text-gray-600 mb-6 space-y-2">
            <li>{{ __('installer.✓ Database connection details') }}</li>
            <li>{{ __('installer.✓ Administrator account information') }}</li>
            <li>{{ __('installer.✓ Write permissions on required directories') }}</li>
        </ul>

        <a href="{{ route('installer.requirements') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            {{ __('installer.Get Started →') }}
        </a>
    </div>
@endsection