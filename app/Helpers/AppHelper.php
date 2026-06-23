<?php

if (! function_exists('syncModulePermission')) {
    function syncModulePermission(): void
    {
        // No-op: module system has been removed.
    }
}

if (! function_exists('theme_asset')) {
    function theme_asset(string $path): string
    {
        return asset('themes/default/assets/' . ltrim($path, '/'));
    }
}

if (! function_exists('getFooterMenu')) {
    function getFooterMenu(): \Illuminate\Support\Collection
    {
        return collect();
    }
}
