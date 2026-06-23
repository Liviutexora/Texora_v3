<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    |
    | When demo mode is enabled, most edit and delete operations will be
    | disabled to prevent accidental changes in demo/staging environments.
    |
    */

    'enabled' => env('DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Restricted Actions
    |--------------------------------------------------------------------------
    |
    | List of actions that should be disabled in demo mode.
    | You can customize this list based on your needs.
    |
    */

    'restricted_actions' => [
        'edit',
        'delete',
        'create',
        'update',
        'destroy',
        'uninstall',
        'deactivate',
        'activate',
        'toggle',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Actions in Demo Mode
    |--------------------------------------------------------------------------
    |
    | Actions that are still allowed even in demo mode.
    | These are typically read-only operations.
    |
    */

    'allowed_actions' => [
        'view',
        'show',
        'index',
        'list',
        'export',
        'download',
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo Mode Message
    |--------------------------------------------------------------------------
    |
    | The message to display when users try to perform restricted actions.
    |
    */

    'restricted_message' => 'This action is disabled in demo mode.',

    /*
    |--------------------------------------------------------------------------
    | Show Demo Banner
    |--------------------------------------------------------------------------
    |
    | Whether to show a banner at the top of the admin panel indicating
    | that demo mode is active.
    |
    */

    'show_banner' => true,

    /*
    |--------------------------------------------------------------------------
    | Banner Message
    |--------------------------------------------------------------------------
    |
    | The message to display in the demo mode banner.
    |
    */

    'banner_message' => 'Demo Mode: Edit and delete operations are disabled.',

    /*
    |--------------------------------------------------------------------------
    | Excluded Resources
    |--------------------------------------------------------------------------
    |
    | Resources that should NOT be restricted in demo mode.
    | Add resource class names here to allow full access even in demo mode.
    |
    */

    'excluded_resources' => [
        // Example: \App\Filament\Resources\Settings\SettingResource::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Actions by Resource
    |--------------------------------------------------------------------------
    |
    | Specific actions that should remain enabled for specific resources
    | even in demo mode. Format: 'ResourceClass' => ['action1', 'action2']
    |
    */

    'resource_allowed_actions' => [
        // Example: \App\Filament\Resources\Modules\ModuleResource::class => ['view', 'settings'],
    ],
];

