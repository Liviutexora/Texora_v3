@extends('installer.layout')

@section('title', __('installer.Administrator Account'))

@section('content')
    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('installer.Create Administrator Account') }}</h3>
    
    <form method="POST" action="{{ route('installer.admin.save') }}" class="space-y-4">
        @csrf
        
      <div>
            <label for="site_title" class="block text-sm font-medium text-gray-700">{{ __('installer.Site Title') }}</label>
            <input type="text" 
                name="site_title" 
                id="site_title" 
                value="{{ old('site_title') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                required>
        </div>
        <div>
            <label for="site_description" class="block text-sm font-medium text-gray-700">{{ __('installer.Site Description') }}</label>
            <textarea
                name="site_description"
                id="site_description"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                >{{ old('site_description') }}</textarea>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('installer.Name') }}</label>
            <input type="text"
                   name="name"
                   id="name"
                   value="{{ old('name') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                   required>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">{{ __('installer.Email Address') }}</label>
            <input type="email"
                   name="email"
                   id="email"
                   value="{{ old('email') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                   required>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('installer.Password') }}</label>
            <input type="password"
                   name="password"
                   id="password"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                   required>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('installer.Confirm Password') }}</label>
            <input type="password"
                   name="password_confirmation"
                   id="password_confirmation"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                   required>
        </div>

         <div>
            <div class="my-4 flex items-center">
                <input name="start_with_demo_data" value="1" id="start_with_demo_data" type="checkbox" 
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded cursor-pointer">
                <label for="start_with_demo_data" class="ml-2 text-sm text-gray-700 cursor-pointer">{{ __('installer.Start with Demo Data') }}</label>
            </div>
        </div>


        <div class="mt-6 flex justify-between">
            <a href="{{ route('installer.database') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                {{ __('installer.← Back') }}
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                {{ __('installer.Create Account & Install →') }}
            </button>
        </div>
    </form>
@endsection