<?php

namespace App\Services\Pwa;

use App\Services\Pwa\Core\PwaManifestService;
use App\Services\Pwa\Core\PwaMetaTagService;
use App\Services\Pwa\Core\PwaIconFallbackResolver;
use App\Services\Pwa\Core\PwaRouteRegistrar;
use App\Services\Pwa\Core\PwaServiceWorkerService;
use App\Services\Pwa\Core\PwaStaticArtifactPublisher;
use App\Services\Pwa\Core\PwaVersionService;
use App\Services\Pwa\View\Components\PwaHead;
use App\Services\Pwa\View\Components\PwaInstallButton;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PwaCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('pwa.php'), 'pwa');

        $this->app->singleton(PwaManifestService::class);
        $this->app->singleton(PwaMetaTagService::class);
        $this->app->singleton(PwaIconFallbackResolver::class);
        $this->app->singleton(PwaStaticArtifactPublisher::class);
        $this->app->singleton(PwaServiceWorkerService::class);
        $this->app->singleton(PwaVersionService::class);
        $this->app->singleton(PwaRouteRegistrar::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'pwa');

        Blade::component('pwa-head', PwaHead::class);
        Blade::component('pwa-install-button', PwaInstallButton::class);

        app(PwaRouteRegistrar::class)->register();
    }
}