@extends('installer.layout')

@section('title', __('installer.Permissions'))

@section('content')
    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('installer.Directory Permissions') }}</h3>
    
    <div class="space-y-2">
        @foreach($permissions as $permission)
            <div class="flex items-center justify-between p-2 rounded {{ $permission['check'] ? 'bg-green-50' : 'bg-red-50' }}">
                
                {{-- LEFT SIDE WITH WRAPPING FIX --}}
                <div class="min-w-0">
                    <span class="text-sm font-medium {{ $permission['check'] ? 'text-green-700' : 'text-red-700' }}">
                        {{ $permission['name'] }}
                    </span>

                    {{-- PATH: WRAPS CORRECTLY --}}
                    <span class="text-xs text-gray-500 block whitespace-normal break-words">
                        {{ $permission['path'] }}
                    </span>
                </div>

                {{-- RIGHT SIDE --}}
                <span class="text-sm font-medium {{ $permission['check'] ? 'text-green-700' : 'text-red-700' }}">
                    {{ $permission['check'] ? __('installer.✓ Writable') : __('installer.✗ Not Writable') }}
                </span>
            </div>
        @endforeach
    </div>

    @if(collect($permissions)->every(fn($p) => $p['check']))
        <div class="mt-6 text-right">
            <a href="{{ route('installer.database') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                {{ __('Next') }} →
            </a>
        </div>
    @else
        <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
            {{ __('installer.Please fix the directory permissions before proceeding. Make sure the directories are writable by the web server.') }}
        </div>
    @endif
@endsection
