<?php

namespace App\Providers\Filament;

use App\Filament\Tenant\Pages\TenantDashboard;
use App\Http\Middleware\SetTenantFromUser;
use App\Http\Middleware\TenantAuthenticate;
use Filament\Http\Middleware\Authenticate;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $siteName   = config('app.name');
        $logoHeight = '2rem';
        $faviconUrl = null;
        try {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            if ($pdo instanceof \PDO) {
                $siteName    = \App\Models\Setting::get('site_name', $siteName) ?? $siteName;
                $height      = (float) (\App\Models\Setting::get('site_admin_logo_height', 2) ?: 2);
                $logoHeight  = $height . 'rem';
                $faviconPath = \App\Models\Setting::get('site_favicon');
                $faviconUrl  = $faviconPath ? asset('storage/' . $faviconPath) : null;
            }
        } catch (\Exception) {
            // DB not ready — keep default
        }

        return $panel
            ->id('tenant')
            ->path('manage')
            ->brandName($siteName)
            // No panel-specific login — unauthenticated users go to /login
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight($logoHeight)
            ->favicon($faviconUrl)
            ->colors([
                'primary' => Color::Violet,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->plugins([
                FilamentFullCalendarPlugin::make()
                    ->selectable(false)
                    ->editable(false),
            ])
            ->discoverResources(
                in: app_path('Filament/Tenant/Resources'),
                for: 'App\\Filament\\Tenant\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Tenant/Pages'),
                for: 'App\\Filament\\Tenant\\Pages'
            )
            ->pages([
                TenantDashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Tenant/Widgets'),
                for: 'App\\Filament\\Tenant\\Widgets'
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetTenantFromUser::class,
            ])
            ->authMiddleware([
                TenantAuthenticate::class,
            ])
            ->authGuard('web')
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::TOPBAR_BEFORE,
                fn (): string => view('filament.components.demo-mode-banner')->render()
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_BEFORE,
                fn (): string => session()->has('impersonate_tenant_id')
                    ? view('filament.components.impersonation-topbar')->render()
                    : ''
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_BEFORE,
                fn (): string => \App\Support\TenantContext::current()
                    ? view('filament.components.past-due-banner')->render()
                    : ''
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_BEFORE,
                fn (): string => \App\Support\TenantContext::current()
                    ? view('filament.components.trial-banner')->render()
                    : ''
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): string => \App\Support\TenantContext::current()
                    ? view('filament.components.plan-badge')->render()
                    : ''
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    .fi-sidebar { background-color: #ffffff !important; }
                    .fi-sidebar-header { background-color: #ffffff !important; }
                    .dark .fi-sidebar { background-color: #111827 !important; }
                    .dark .fi-sidebar-header { background-color: #111827 !important; }
                    #database-notifications.fi-modal-slide-over > .fi-modal-window-ctn > .fi-modal-window { position: fixed !important; top: 0 !important; bottom: 0 !important; right: 0 !important; left: auto !important; }
                </style>'
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string =>
                    '<link rel="stylesheet" href="' . \Illuminate\Support\Facades\Vite::asset('resources/css/tenant-panel.css') . '">' .
                    '<link rel="stylesheet" href="' . \Illuminate\Support\Facades\Vite::asset('resources/css/fb-classes.css') . '">'
            );
    }
}
