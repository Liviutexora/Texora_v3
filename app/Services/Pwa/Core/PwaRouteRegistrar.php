<?php

namespace App\Services\Pwa\Core;

use App\Services\Pwa\Http\Controllers\ManifestController;
use App\Services\Pwa\Http\Controllers\ServiceWorkerController;
use App\Services\Pwa\Http\Controllers\IconController;
use App\Services\Pwa\Http\Controllers\VersionController;
use Illuminate\Support\Facades\Route;

class PwaRouteRegistrar
{
    public function register(): void
    {
        if (! config('pwa.enabled', true)) {
            return;
        }

        $prefix = trim((string) config('pwa.route.prefix', 'pwa'), '/');
        $name = (string) config('pwa.route.as', 'pwa.');
        $middleware = (array) config('pwa.route.middleware', ['web']);

        Route::middleware($middleware)
            ->prefix($prefix)
            ->as($name)
            ->group(function (): void {
                Route::get('manifest.webmanifest', ManifestController::class)->name('manifest');
                Route::get('icons/{purpose}/{size}.png', IconController::class)
                    ->whereIn('purpose', ['any', 'maskable'])
                    ->whereIn('size', ['192', '512'])
                    ->name('icon');
                Route::get(config('pwa.service_worker.filename', 'sw.js'), ServiceWorkerController::class)->name('service-worker');
                Route::get(config('pwa.version.filename', 'version.json'), VersionController::class)->name('version');
            });
    }
}