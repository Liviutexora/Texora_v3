<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\IpRestrictionMiddleware;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\SetTenantFromUser;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::aliasMiddleware('ip.restrict', IpRestrictionMiddleware::class);
        Route::aliasMiddleware('check.maintenance.mode', CheckMaintenanceMode::class);
        Route::aliasMiddleware('identify.tenant', IdentifyTenant::class);
        Route::aliasMiddleware('set.tenant.from.user', SetTenantFromUser::class);
    }
}
