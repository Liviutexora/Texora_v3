<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Profile;
use App\Helpers\DemoModeHelper;
use App\Models\Setting;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        if (! DemoModeHelper::isEnabled()) {
            return;
        }

        $msg = DemoModeHelper::getRestrictedMessage();

        $disable = fn ($action) => $action->disabled()->color('gray')->tooltip($msg);

        CreateAction::configureUsing($disable);
        DeleteAction::configureUsing($disable);
        DeleteBulkAction::configureUsing($disable);
        ForceDeleteAction::configureUsing($disable);
        ForceDeleteBulkAction::configureUsing($disable);
        RestoreAction::configureUsing($disable);
        RestoreBulkAction::configureUsing($disable);

        Action::configureUsing(function (Action $action) use ($msg): void {
            if (! in_array($action->getName(), [
                'save', 'create', 'saveAndCreateAnother',
                'delete', 'forceDelete', 'restore',
            ])) {
                return;
            }

            $action
                ->color('gray')
                ->tooltip($msg)
                ->action(function () use ($msg): void {
                    \Filament\Notifications\Notification::make()
                        ->title(__('Demo Mode'))
                        ->body($msg)
                        ->warning()
                        ->send();
                });
        });
    }

    public function panel(Panel $panel): Panel
    {
        $siteName   = env('APP_NAME');
        $logoHeight = '2rem';
        $faviconUrl = null;
        try {
            $pdo = DB::connection()->getPdo();
            if ($pdo instanceof \PDO) {
                $siteName   = Setting::get('site_name', config('app.name')) ?? $siteName;
                $height     = (float) (Setting::get('site_admin_logo_height', 2) ?: 2);
                $logoHeight = $height . 'rem';
                $faviconPath = Setting::get('site_favicon');
                $faviconUrl  = $faviconPath ? asset('storage/' . $faviconPath) : null;
            }
        } catch (\Exception $e) {
            // DB not ready — keep default
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName($siteName)
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight($logoHeight)
            ->favicon($faviconUrl)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('20rem')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->colors([
                'primary' => Color::Violet,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                Profile::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(__('Profile'))
                    ->icon('heroicon-o-user')
                    ->url(fn (): string => Profile::getUrl(panel: 'admin')),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
                \App\Http\Middleware\RedirectTenantOwnerFromAdmin::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>#database-notifications.fi-modal-slide-over > .fi-modal-window-ctn > .fi-modal-window { position: fixed !important; top: 0 !important; bottom: 0 !important; right: 0 !important; left: auto !important; }</style>'
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.scripts.echo')->render()
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_BEFORE,
                fn (): string => view('filament.components.demo-mode-banner')->render()
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => config('demo.enabled')
                    ? view('filament.pages.auth.login-demo-panel')->render()
                    : ''
            );
    }
}
