<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstallerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Match only exact installer routes: /install or /install/*
        // Using path() which strips the subdirectory prefix automatically.
        $path = $request->path();
        $isInstallRoute = $path === 'install' || str_starts_with($path, 'install/');

        // During installation, force file-based sessions
        if (!file_exists(base_path('.installed'))) {
            config([
                'session.driver' => 'file',
                'cache.default'  => 'array',
            ]);

            if (!$isInstallRoute) {
                return redirect()->route('installer.index');
            }
        }

        // Block installer routes once installation is complete
        if (file_exists(base_path('.installed')) && $isInstallRoute) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}