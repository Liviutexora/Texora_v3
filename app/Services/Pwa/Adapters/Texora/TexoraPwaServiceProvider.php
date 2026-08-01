<?php

namespace App\Services\Pwa\Adapters\Texora;

use App\Services\Pwa\Contracts\PwaBrandingRepositoryInterface;
use App\Services\Pwa\Contracts\PwaContextResolverInterface;
use App\Services\Pwa\Contracts\PwaVersionSourceInterface;
use App\Services\Pwa\View\Components\PwaHead;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\ServiceProvider;

class TexoraPwaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PwaBrandingRepositoryInterface::class, TexoraPwaBrandingRepository::class);
        $this->app->singleton(PwaContextResolverInterface::class, TexoraPwaContextResolver::class);
        $this->app->singleton(PwaVersionSourceInterface::class, TexoraVersionSource::class);
    }

    public function boot(): void
    {
        if (! config('pwa.enabled', true) || ! class_exists(FilamentView::class)) {
            return;
        }

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => app(PwaHead::class)->render()->render(),
        );
    }
}