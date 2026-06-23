<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('HTML Preview') }}</title>
    <script src="{{ asset('themes/default/assets/js/tailwindcss.js') }}"></script>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('HTML Content Preview') }}</h1>
                <button onclick="window.close()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-medium">
                    {{ __('Close') }}
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                {{ __('This is a preview of how your HTML content will appear. Tailwind CSS is loaded for styling.') }}
            </p>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            @if(!empty($html))
                <div class="preview-content">
                    {!! $html !!}
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg">{{ __('No content to preview.') }}</p>
                    <p class="text-gray-400 text-sm mt-2">{{ __('Enter some HTML in the editor and click Preview again.') }}</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>

