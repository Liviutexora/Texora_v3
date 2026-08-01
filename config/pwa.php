<?php

return [
    'enabled' => true,

    'vite_entry' => 'resources/js/pwa/index.js',

    'route' => [
        'prefix' => 'pwa',
        'as' => 'pwa.',
        'middleware' => ['web'],
    ],

    'manifest' => [
        'display' => 'standalone',
        'display_override' => ['fullscreen', 'standalone', 'minimal-ui'],
        'orientation' => 'portrait',
    ],

    'apple' => [
        'capable' => true,
        'status_bar_style' => 'default',
    ],

    'service_worker' => [
        'filename' => 'sw.js',
        'cache_prefix' => 'texora-pwa',
    ],

    'version' => [
        'filename' => 'version.json',
        'check_interval_ms' => 300000,
    ],

    'install' => [
        'label' => 'Instaleaza aplicatia',
        'open_label' => 'Deschide aplicatia',
        'ios_instructions' => 'In Safari, apasa Share si apoi Add to Home Screen.',
        'unsupported_message' => 'Instalarea automata nu este disponibila in acest browser.',
        'ios_label' => 'Cum instalez pe iPhone',
    ],

    'defaults' => [
        'app_name' => env('APP_NAME', 'Laravel App'),
        'short_name' => env('APP_NAME', 'Laravel'),
        'theme_color' => '#111827',
        'background_color' => '#ffffff',
    ],
];