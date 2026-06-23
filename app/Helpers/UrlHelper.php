<?php

declare(strict_types=1);

if (! function_exists('base_url')) {
    /**
     * Full URL to the application root with an optional path (used by theme menus, installer assets).
     */
    function base_url(string $path = '/'): string
    {
        $base = rtrim((string) config('app.url'), '/');
        if ($path === '' || $path === '/') {
            return $base . '/';
        }

        return $base . '/' . ltrim($path, '/');
    }
}
