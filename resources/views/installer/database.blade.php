@extends('installer.layout')

@section('title', __('installer.Database Configuration'))

@section('content')
    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('installer.Database Configuration') }}</h3>
    
    <form method="POST" action="{{ route('installer.database.save') }}" class="space-y-4">
        @csrf
        
        <div>
            <label for="database_hostname" class="block text-sm font-medium text-gray-700">{{ __('installer.Host') }}</label>
            <input type="text" 
                   name="database_hostname" 
                   id="database_hostname" 
                   value="{{ old('database_hostname', '127.0.0.1') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                   required>
        </div>

        <div>
            <label for="database_port" class="block text-sm font-medium text-gray-700">{{ __('installer.Port') }}</label>
            <input type="text" 
                   name="database_port" 
                   id="database_port" 
                   value="{{ old('database_port', '3306') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                   required>
        </div>

        <div>
            <label for="database_name" class="block text-sm font-medium text-gray-700">{{ __('installer.Database Name') }}</label>
            <input type="text" 
                   name="database_name" 
                   id="database_name" 
                   value="{{ old('database_name', 'slotara') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                   required>
        </div>

        <div>
            <label for="database_username" class="block text-sm font-medium text-gray-700">{{ __('installer.Username') }}</label>
            <input type="text" 
                   name="database_username" 
                   id="database_username" 
                   value="{{ old('database_username', 'root') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                   required>
        </div>

        <div>
            <label for="database_password" class="block text-sm font-medium text-gray-700">{{ __('installer.Password') }}</label>
            <input type="password" 
                   name="database_password" 
                   id="database_password" 
                   value="{{ old('database_password') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border">
            <p class="mt-1 text-sm text-gray-500">{{ __('installer.Leave blank if no password') }}</p>
        </div>

        <div class="mt-6 flex justify-between">
            <a href="{{ route('installer.permissions') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                {{ __('installer.← Back') }}
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                {{ __('installer.Test Connection & Continue →') }}
            </button>
        </div>
    </form>
@endsection