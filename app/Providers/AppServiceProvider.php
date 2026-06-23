<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use App\Models\Setting;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\LanguageSwitch\Events\LocaleChanged;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Event;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends DatabaseAwareServiceProvider
{
    public function register(): void
    {
        // Auto-detect subdirectory path and set APP_URL dynamically
        // This must happen early, before routes are generated
        if (app()->runningInConsole() === false && isset($_SERVER['SCRIPT_NAME'])) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

            // Get the script name to determine the subdirectory
            // When accessed via /pacific/modular-starter/, the SCRIPT_NAME will be
            // /pacific/modular-starter/public/index.php
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

            // Extract subdirectory from script name
            // Remove /public/index.php to get the subdirectory
            // $subdirectory = preg_replace('#/public/index\.php$#', '', $scriptName);
            $subdirectory = '/';

            // Clean up subdirectory
            if ($subdirectory === '/' || $subdirectory === '\\' || empty($subdirectory)) {
                $subdirectory = '';
            }

            // Build root URL with subdirectory
            $rootUrl = $scheme.'://'.$host.$subdirectory;

            // Update config FIRST (before using URL facade)
            config(['app.url' => $rootUrl]);
            config(['filesystems.disks.public.url' => $rootUrl.'/storage']);
        }

    }

    public function boot(): void
    {
        FilamentShield::buildPermissionKeyUsing(function (string $entity, string $affix, string $subject, string $case, string $separator): string {
            return FilamentShield::defaultPermissionKeyBuilder($affix, $separator, $subject, $case);
        });

        // Force Laravel to not include index.php in generated URLs
        // This removes index.php from all generated URLs
        if (app()->runningInConsole() === false) {
            // Remove index.php from script name if present
            if (isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], 'index.php')) {
                $_SERVER['SCRIPT_NAME'] = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
            }

            // Also update REQUEST_URI to remove index.php if present
            if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/index.php')) {
                $_SERVER['REQUEST_URI'] = str_replace('/index.php', '', $_SERVER['REQUEST_URI']);
            }
        }

        // Ensure root URL is set for assets (runs on every request)
        // This ensures asset URLs include the subdirectory path
        // Guard: only run subdirectory detection when SCRIPT_NAME looks like a real PHP
        // script path (contains 'index.php' or ends with '.php'). The PHP built-in server
        // sets SCRIPT_NAME to the request URI for non-file routes, which must NOT be
        // treated as a subdirectory.
        $rawScriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        // Only treat as a real app entry point when SCRIPT_NAME contains 'index.php'.
        // Valet sets SCRIPT_NAME to the absolute path of its own server.php
        // (e.g. /Users/…/.composer/vendor/laravel/valet/server.php), which must
        // not be mistaken for a web-relative subdirectory path.
        $isRealScriptName = str_contains($rawScriptName, 'index.php') && !str_contains($rawScriptName, '/vendor/');
        if (app()->runningInConsole() === false && isset($_SERVER['SCRIPT_NAME']) && $isRealScriptName) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            // Remove index.php from script name if still present
            $scriptName = str_replace('/index.php', '', $scriptName);
            $subdirectory = preg_replace('#/public$#', '', $scriptName);

            if (! empty($subdirectory) && $subdirectory !== '/') {
                $rootUrl = $scheme.'://'.$host.$subdirectory;

                // Force root URL again in boot() to ensure it's set for asset generation
                URL::forceRootUrl($rootUrl);

                // Set asset root directly on URL generator instance using reflection
                // This ensures asset() helper uses the correct base URL with subdirectory
                try {
                    $urlGenerator = app('url');
                    $reflection = new \ReflectionClass($urlGenerator);
                    if ($reflection->hasProperty('assetRoot')) {
                        $property = $reflection->getProperty('assetRoot');
                        $property->setAccessible(true);
                        $property->setValue($urlGenerator, $rootUrl);
                    }
                } catch (\Exception $e) {
                    // If reflection fails, continue - formatRoot() should still work
                }

                // Update config to ensure consistency
                config(['app.url' => $rootUrl]);
                config(['filesystems.disks.public.url' => $rootUrl.'/storage']);

                // Configure Livewire to use subdirectory for assets
                // Livewire uses route URI (/livewire/livewire.js) which doesn't include subdirectory
                // Solution: Use url() helper to generate the path with subdirectory and set it
                $livewirePath = url('/livewire/livewire.js');
                // Extract just the path part (without domain, with subdirectory)
                $livewirePathOnly = parse_url($livewirePath, PHP_URL_PATH);
                // Set livewire.asset_url to the path so Livewire uses it instead of route URI
                config(['livewire.asset_url' => $livewirePathOnly]);

                // Also ensure Livewire update URI includes subdirectory
                if (! config('livewire.update_uri')) {
                    config(['livewire.update_uri' => $subdirectory.'/livewire/update']);
                }
            } else {
                // Even without subdirectory, ensure index.php is not in URLs
                $rootUrl = $scheme.'://'.$host;
                URL::forceRootUrl($rootUrl);
                config(['app.url' => $rootUrl]);
            }

            if ($this->app->runningInConsole() && app()->environment('production')) {
                // Run once on first deploy or when needed
                try {
                    Artisan::call('livewire:publish', ['--assets' => true]);
                } catch (\Throwable $e) {
                    // Optional: log error instead of breaking all Artisan commands
                    Log::warning('Failed to publish Livewire assets', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if (file_exists(base_path('.installed'))) {
            // Override social login config from database
            $this->bootWithDatabase(function () {
                // Apply session timeout from settings
                $sessionTimeout = Setting::get('session_timeout');
                if ($sessionTimeout && is_numeric($sessionTimeout) && $sessionTimeout > 0) {
                    config(['session.lifetime' => (int) $sessionTimeout]);
                }

                // Update Google OAuth config from database
                $googleClientId = Setting::get('google_client_id');
                $googleClientSecret = Setting::get('google_client_secret');
                $googleRedirectUri = Setting::get('google_redirect_uri');

                if ($googleClientId || $googleClientSecret || $googleRedirectUri) {
                    config([
                        'services.google.client_id' => $googleClientId ?: config('services.google.client_id'),
                        'services.google.client_secret' => $googleClientSecret ?: config('services.google.client_secret'),
                        'services.google.redirect' => $googleRedirectUri ?: config('services.google.redirect'),
                    ]);
                }

                // Update Facebook OAuth config from database
                $facebookClientId = Setting::get('facebook_client_id');
                $facebookClientSecret = Setting::get('facebook_client_secret');
                $facebookRedirectUri = Setting::get('facebook_redirect_uri');

                if ($facebookClientId || $facebookClientSecret || $facebookRedirectUri) {
                    config([
                        'services.facebook.client_id' => $facebookClientId ?: config('services.facebook.client_id'),
                        'services.facebook.client_secret' => $facebookClientSecret ?: config('services.facebook.client_secret'),
                        'services.facebook.redirect' => $facebookRedirectUri ?: config('services.facebook.redirect'),
                    ]);
                }

                // Update GitHub OAuth config from database
                $githubClientId = Setting::get('github_client_id');
                $githubClientSecret = Setting::get('github_client_secret');
                $githubRedirectUri = Setting::get('github_redirect_uri');

                if ($githubClientId || $githubClientSecret || $githubRedirectUri) {
                    config([
                        'services.github.client_id' => $githubClientId ?: config('services.github.client_id'),
                        'services.github.client_secret' => $githubClientSecret ?: config('services.github.client_secret'),
                        'services.github.redirect' => $githubRedirectUri ?: config('services.github.redirect'),
                    ]);
                }

                // Update LinkedIn OAuth config from database
                $linkedinClientId = Setting::get('linkedin_client_id');
                $linkedinClientSecret = Setting::get('linkedin_client_secret');
                $linkedinRedirectUri = Setting::get('linkedin_redirect_uri');

                if ($linkedinClientId || $linkedinClientSecret || $linkedinRedirectUri) {
                    config([
                        'services.linkedin.client_id' => $linkedinClientId ?: config('services.linkedin.client_id'),
                        'services.linkedin.client_secret' => $linkedinClientSecret ?: config('services.linkedin.client_secret'),
                        'services.linkedin.redirect' => $linkedinRedirectUri ?: config('services.linkedin.redirect'),
                    ]);
                }

                // Update S3 filesystem config from database (for Filament FileUpload)
                $filesystemDisk = Setting::get('filesystem_disk', config('filesystems.default', 'public'));
                if ($filesystemDisk === 's3') {
                    config([
                        'filesystems.disks.s3.key' => Setting::get('s3_access_key_id', config('filesystems.disks.s3.key')),
                        'filesystems.disks.s3.secret' => Setting::get('s3_secret_access_key', config('filesystems.disks.s3.secret')),
                        'filesystems.disks.s3.region' => Setting::get('s3_default_region', config('filesystems.disks.s3.region')),
                        'filesystems.disks.s3.bucket' => Setting::get('s3_bucket', config('filesystems.disks.s3.bucket')),
                        'filesystems.disks.s3.url' => Setting::get('s3_url', config('filesystems.disks.s3.url')),
                        'filesystems.disks.s3.endpoint' => Setting::get('s3_endpoint', config('filesystems.disks.s3.endpoint')),
                        'filesystems.disks.s3.use_path_style_endpoint' => (bool) Setting::get('s3_use_path_style_endpoint', config('filesystems.disks.s3.use_path_style_endpoint', false)),
                    ]);
                }

                // OpenAI: use API key from admin Settings when set (otherwise keep .env config).
                $openaiKey = Setting::get('openai_api_key');
                if ($openaiKey !== null && trim((string) $openaiKey) !== '') {
                    config(['services.openai.key' => trim((string) $openaiKey)]);
                }
                $openaiModel = Setting::get('openai_model');
                if ($openaiModel !== null && trim((string) $openaiModel) !== '') {
                    config(['services.openai.model' => trim((string) $openaiModel)]);
                }

                // Stripe: load keys from DB when configured (overrides .env)
                $stripeKey    = Setting::get('stripe_key');
                $stripeSecret = Setting::get('stripe_secret');
                $stripeWebhook = Setting::get('stripe_webhook_secret');
                if ($stripeKey || $stripeSecret || $stripeWebhook) {
                    config([
                        'services.stripe.key'            => $stripeKey    ?: config('services.stripe.key'),
                        'services.stripe.secret'         => $stripeSecret  ?: config('services.stripe.secret'),
                        'services.stripe.webhook_secret' => $stripeWebhook ?: config('services.stripe.webhook_secret'),
                    ]);
                }

                // SMTP: load mail settings from DB when configured
                $mailHost = Setting::get('mail_host');
                if ($mailHost) {
                    config([
                        'mail.mailers.smtp.host'       => $mailHost,
                        'mail.mailers.smtp.port'       => Setting::get('mail_port', config('mail.mailers.smtp.port', 587)),
                        'mail.mailers.smtp.username'   => Setting::get('mail_username', config('mail.mailers.smtp.username')),
                        'mail.mailers.smtp.password'   => Setting::get('mail_password', config('mail.mailers.smtp.password')),
                        'mail.mailers.smtp.encryption' => Setting::get('mail_encryption', config('mail.mailers.smtp.encryption', 'tls')),
                        'mail.from.address'            => Setting::get('mail_from_address', config('mail.from.address')),
                        'mail.from.name'               => Setting::get('mail_from_name', config('mail.from.name')),
                        'mail.default'                 => 'smtp',
                    ]);
                }

                // Auto-create demo users when DEMO_MODE=true.
                // This runs BEFORE the saving observer is registered so
                // firstOrCreate inside the seeder is not blocked.
                if (config('demo.enabled')) {
                    \Database\Seeders\DemoUserSeeder::ensureDemoUsersExist();
                }

                // Block all writes at the Eloquent level in demo mode.
                // Registered after the seeder so demo-user creation is unaffected.
                // Model::saving() only fires for the base class, not subclasses — must use
                // Event::listen with wildcards to intercept saves across all models.
                if (config('demo.enabled')) {
                    \Illuminate\Support\Facades\Event::listen('eloquent.saving: *', fn () => false);
                    \Illuminate\Support\Facades\Event::listen('eloquent.deleting: *', fn () => false);
                }

            });
        }

        // Configure Filament language switch plugin for all panels
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(SetLocale::enabledLocales())
                ->visible(insidePanels: true, outsidePanels: false);
        });

        // Persist panel language changes to the user's profile
        Event::listen(function (LocaleChanged $event) {
            if ($user = auth()->user()) {
                $user->update(['locale' => $event->locale]);
            }
        });

        $this->registerLivewireComponents();
        Filament::serving(function () {
            // File Manager is now handled by the FileManager Filament page
            // which automatically appears in navigation for super_admin users

            // Filament::registerNavigationItems([
            //     NavigationItem::make('Cache & Maintenance')
            //         ->url('#')
            //         ->icon('heroicon-o-wrench')
            //         ->sort(175)
            //         ->group('System'),
            // ]);

            // Filament::registerNavigationItems([
            //     NavigationItem::make('Version Management')
            //         ->url('#')
            //         ->icon('heroicon-o-cube-transparent')
            //         ->sort(185)
            //         ->group('System'),
            // ]);
        });
    }

    protected function registerLivewireComponents(): void
    {
        // Livewire components registration
    }
}
