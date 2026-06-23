@extends('installer.layout')

@section('title', __('installer.Requirements'))

@section('content')
    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('installer.Server Requirements') }}</h3>
    
    <div class="space-y-2">
        @foreach($requirements as $requirement)
            <div class="flex items-center justify-between p-2 rounded {{ $requirement['check'] ? 'bg-green-50' : 'bg-red-50' }}">
                <span class="text-sm {{ $requirement['check'] ? 'text-green-700' : 'text-red-700' }}">
                    {{ $requirement['name'] }}
                </span>
                <span class="text-sm font-medium {{ $requirement['check'] ? 'text-green-700' : 'text-red-700' }}">
                    {{ $requirement['check'] ? __('installer.✓ Passed') : __('installer.✗ Failed') }}
                </span>
            </div>
        @endforeach
    </div>

    @if(collect($requirements)->every(fn($r) => $r['check']))
        <div class="mt-6 text-right">
            <a href="{{ route('installer.permissions') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                {{ __('Next') }} →
            </a>
        </div>
    @else
        <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
            {{ __('installer.Please fix the requirements before proceeding.') }}
        </div>
    @endif
@endsection