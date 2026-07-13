<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasPagePermissions;
use App\Helpers\DemoModeHelper;
use App\Models\PasswordPolicy;
use App\Models\Setting;
use App\Support\LocalisationOptions;
use App\Services\EmailTemplateService;
use App\Services\FileUploadService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class Settings extends Page implements HasForms
{
    use HasPagePermissions;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected string $view = 'filament.pages.settings';

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 151;

    protected static ?string $navigationLabel = 'Settings';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearCache')
                ->label(__('Clear Cache'))
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading(__('Clear application cache?'))
                ->modalDescription(__('This will clear the application, config, route, and view caches.'))
                ->action(function (): void {
                    try {
                        Artisan::call('optimize:clear');

                        Notification::make()
                            ->title(__('Cache cleared successfully'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Failed to clear cache: ' . $e->getMessage());

                        Notification::make()
                            ->title(__('Failed to clear cache'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->button(),

            Action::make('runQueueWork')
                ->label(__('Run Queue Work'))
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('Run queue worker?'))
                ->modalDescription(__('This will start a queue worker that keeps running in daemon mode. To stop it, you must stop the process manually.'))
                ->action(function (): void {
                    try {
                        $artisanPath = base_path('artisan');

                        $cmd = sprintf(
                            'nohup %s %s queue:work --daemon --sleep=3 --tries=3 > /dev/null 2>&1 &',
                            escapeshellarg(PHP_BINARY),
                            escapeshellarg($artisanPath)
                        );

                        $process = new Process(['sh', '-c', $cmd], base_path());
                        $process->start();

                        Notification::make()
                            ->title(__('Queue worker started (daemon)'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Failed to run queue worker: ' . $e->getMessage());

                        Notification::make()
                            ->title(__('Failed to run queue worker'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->button(),
        ];
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // Branding
            'site_name'              => Setting::get('site_name', config('app.name')),
            'site_admin_logo_height' => Setting::get('site_admin_logo_height', 2),
            'site_logo'              => Setting::get('site_logo'),
            'site_favicon'           => Setting::get('site_favicon'),

            // Email (SMTP) — password is write-only
            'contact_email'     => Setting::get('contact_email', Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS'))),
            'mail_host'         => Setting::get('mail_host', env('MAIL_HOST')),
            'mail_port'         => Setting::get('mail_port', env('MAIL_PORT', 587)),
            'mail_encryption'   => Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls')),
            'mail_username'     => Setting::get('mail_username', env('MAIL_USERNAME')),
            'mail_password'     => null,
            'mail_from_address' => Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS')),
            'mail_from_name'    => Setting::get('mail_from_name', env('MAIL_FROM_NAME')),

            // System
            'maintenance_mode'         => Setting::get('maintenance_mode', false),
            'maintenance_message'      => Setting::get('maintenance_message'),
            'docs_enabled'             => (bool) Setting::get('docs_enabled', true),
            'timezone'                 => Setting::get('timezone', config('app.timezone')),
            'date_format'              => Setting::get('date_format', 'Y-m-d'),
            'time_format'              => Setting::get('time_format', 'H:i'),
            // Language management
            'enabled_languages'  => array_values(array_filter(array_map('trim', explode(',', Setting::get('enabled_languages', implode(',', \App\Http\Middleware\SetLocale::SUPPORTED_LOCALES)))))),
            // Business registration localisation options
            'allowed_timezones'  => array_values(array_filter(array_map('trim', explode(',', Setting::get('allowed_timezones', ''))))),
            'allowed_currencies' => array_values(array_filter(array_map('trim', explode(',', Setting::get('allowed_currencies', ''))))),

            // Security
            'session_timeout'    => Setting::get('session_timeout', 120),
            'max_login_attempts' => Setting::get('max_login_attempts', 5),
            'lockout_duration'   => Setting::get('lockout_duration', 30),
            'force_https'        => Setting::get('force_https', false),

            // Filesystem — S3 secret is write-only
            'filesystem_disk'          => Setting::get('filesystem_disk', env('FILESYSTEM_DISK', 'public')),
            's3_access_key_id'         => Setting::get('s3_access_key_id', env('S3_ACCESS_KEY_ID')),
            's3_secret_access_key'     => null,
            's3_default_region'        => Setting::get('s3_default_region', env('S3_DEFAULT_REGION')),
            's3_bucket'                => Setting::get('s3_bucket', env('S3_BUCKET')),
            's3_endpoint'              => Setting::get('s3_endpoint', env('S3_ENDPOINT')),
            's3_url'                   => Setting::get('s3_url', env('S3_URL')),
            's3_use_path_style_endpoint' => Setting::get('s3_use_path_style_endpoint', env('S3_USE_PATH_STYLE_ENDPOINT', false)),

            // SMS (Twilio) — auth token is write-only
            'twilio_account_sid'  => Setting::get('twilio_account_sid', env('TWILIO_ACCOUNT_SID')),
            'twilio_auth_token'   => null,
            'twilio_from_number'  => Setting::get('twilio_from_number', env('TWILIO_FROM_NUMBER')),
            'test_sms_to'         => null,

            // Google Calendar OAuth — secret is write-only
            'google_calendar_client_id'     => Setting::get('google_calendar_client_id', env('GOOGLE_CALENDAR_CLIENT_ID', env('GOOGLE_CLIENT_ID'))),
            'google_calendar_client_secret' => null,

            // Payments (Stripe) — write-only: never echo secrets back to the browser
            'stripe_key'             => null,
            'stripe_secret'          => null,
            'stripe_webhook_secret'  => null,
            'stripe_portal_config_id' => Setting::get('stripe_portal_config_id'),

            // Password Policy (pp_ prefix to avoid collision with Setting keys)
            ...(function () {
                $p = PasswordPolicy::first();
                return [
                    'pp_min_length'            => $p?->min_length            ?? 8,
                    'pp_require_uppercase'     => (bool) ($p?->require_uppercase  ?? true),
                    'pp_require_lowercase'     => (bool) ($p?->require_lowercase  ?? true),
                    'pp_require_numbers'       => (bool) ($p?->require_numbers    ?? true),
                    'pp_require_special_chars' => (bool) ($p?->require_special_chars ?? true),
                    'pp_expires_days'          => $p?->expires_days          ?? null,
                    'pp_history_count'         => $p?->history_count         ?? 5,
                    'pp_max_login_attempts'    => $p?->max_login_attempts    ?? 5,
                    'pp_lockout_duration'      => $p?->lockout_duration      ?? 30,
                ];
            })(),

            // reCAPTCHA — secret is write-only
            'google_recaptcha_enabled'    => (bool) Setting::get('google_recaptcha_enabled', false),
            'google_recaptcha_site_key'   => Setting::get('google_recaptcha_site_key'),
            'google_recaptcha_secret_key' => null,

            // SEO — meta & analytics
            'site_description'     => Setting::get('site_description'),
            'site_keywords'        => Setting::get('site_keywords'),
            'site_author'          => Setting::get('site_author'),
            'meta_robots'          => Setting::get('meta_robots', 'index, follow'),
            'google_analytics_id'  => Setting::get('google_analytics_id'),
            'google_tag_manager_id'=> Setting::get('google_tag_manager_id'),
            'facebook_pixel_id'    => Setting::get('facebook_pixel_id'),
            'social_twitter'       => ltrim((string) Setting::get('social_twitter', ''), '@'),

            // Robots.txt
            'robots_txt_content' => Setting::get('robots_txt_content') ?: (function () {
                $path = public_path('robots.txt');

                return file_exists($path) ? file_get_contents($path) : "User-agent: *\nDisallow:";
            })(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([

                        // ── Branding ──────────────────────────────────────────────────────────
                        Tab::make('Branding')
                            ->label(__('Branding'))
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('site_name')
                                            ->label(__('Site Name'))
                                            ->required()
                                            ->helperText(__('Used in email subjects, admin panel title, and notifications.')),

                                        TextInput::make('site_admin_logo_height')
                                            ->numeric()
                                            ->label(__('Logo Height (rem)'))
                                            ->default(2)
                                            ->helperText(__('Controls logo height in the admin sidebar and frontend. Default: 2 rem.')),

                                        FileUpload::make('site_logo')
                                            ->label(__('Logo'))
                                            ->image()
                                            ->placeholder(__('Drag & Drop your files or Browse'))
                                            ->disk(fn () => Setting::get('filesystem_disk', env('FILESYSTEM_DISK', 'public')))
                                            ->directory('settings/logos')
                                            ->visibility('public')
                                            ->preserveFilenames(false)
                                            ->maxSize(2048)
                                            ->helperText(__('Recommended: PNG with transparent background, max 2 MB.'))
                                            ->saveUploadedFileUsing(fn ($file) => FileUploadService::handleFileUpload($file, 'settings/logos')),

                                        FileUpload::make('site_favicon')
                                            ->label(__('Favicon'))
                                            ->image()
                                            ->placeholder(__('Drag & Drop your files or Browse'))
                                            ->disk(fn () => Setting::get('filesystem_disk', env('FILESYSTEM_DISK', 'public')))
                                            ->directory('settings/favicons')
                                            ->visibility('public')
                                            ->preserveFilenames(false)
                                            ->maxSize(512)
                                            ->helperText(__('Recommended: 32×32 PNG/ICO, max 512 KB.'))
                                            ->saveUploadedFileUsing(fn ($file) => FileUploadService::handleFileUpload($file, 'settings/favicons')),
                                    ])->columns(2),
                            ]),

                        // ── SMS (Twilio) ──────────────────────────────────────────────────────
                        Tab::make('SMS')
                            ->label(__('SMS'))
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Section::make(__('Twilio API'))
                                    ->description(fn () => __('Platform-wide Twilio credentials used for all outbound SMS. Get these from twilio.com/console. All tenants share this account — usage is billed to your Twilio balance.'))
                                    ->schema([
                                        TextInput::make('twilio_account_sid')
                                            ->label(__('Account SID'))
                                            ->placeholder('ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
                                            ->helperText(function () {
                                                $val = Setting::get('twilio_account_sid') ?: env('TWILIO_ACCOUNT_SID');
                                                if ($val) {
                                                    return __('Configured: :masked', ['masked' => substr($val, 0, 6) . str_repeat('•', 20) . substr($val, -4)]);
                                                }
                                                return __('Found in Twilio Console → Account Info.');
                                            }),

                                        TextInput::make('twilio_auth_token')
                                            ->label(__('Auth Token'))
                                            ->password()
                                            ->revealable()
                                            ->placeholder(__('Leave blank to keep existing'))
                                            ->helperText(function () {
                                                $val = Setting::get('twilio_auth_token') ?: env('TWILIO_AUTH_TOKEN');
                                                if ($val) {
                                                    return __('Configured — leave blank to keep existing.');
                                                }
                                                return __('Found in Twilio Console → Account Info. Keep this private.');
                                            }),

                                        TextInput::make('twilio_from_number')
                                            ->label(__('From Number'))
                                            ->tel()
                                            ->placeholder('+15551234567')
                                            ->helperText(__('Your Twilio phone number in E.164 format (e.g. +15551234567).')),
                                    ])->columns(2),

                                Section::make(__('Send a Test SMS'))
                                    ->schema([
                                        TextInput::make('test_sms_to')
                                            ->label(__('Recipient Phone'))
                                            ->tel()
                                            ->placeholder('+15551234567'),

                                        Action::make('sendTestSms')
                                            ->label(__('Send Test SMS'))
                                            ->icon('heroicon-o-paper-airplane')
                                            ->action('sendTestSms')
                                            ->color('success')
                                            ->requiresConfirmation()
                                            ->modalHeading(__('Send a test SMS?'))
                                            ->modalSubmitActionLabel(__('Send')),
                                    ])->columns(1),
                            ]),

                        // ── Integrations ──────────────────────────────────────────────────────
                        Tab::make('Integrations')
                            ->label(__('Integrations'))
                            ->icon('heroicon-o-puzzle-piece')
                            ->schema([
                                Section::make(__('Google Calendar API'))
                                    ->description(fn () => __('OAuth credentials for Google Calendar sync. Providers connect their own calendars from Manage → Integrations → Calendar Sync. Create credentials at console.cloud.google.com → APIs & Services → Credentials.') . ' ' . __('OAuth Redirect URI:') . ' ' . url('/manage/calendar/oauth/callback'))
                                    ->schema([
                                        TextInput::make('google_calendar_client_id')
                                            ->label(__('Client ID'))
                                            ->placeholder('xxxxxxxxxx.apps.googleusercontent.com')
                                            ->helperText(function () {
                                                $val = Setting::get('google_calendar_client_id') ?: env('GOOGLE_CALENDAR_CLIENT_ID') ?: env('GOOGLE_CLIENT_ID');
                                                if ($val) {
                                                    return __('Configured: :masked — leave blank to keep existing', ['masked' => substr($val, 0, 8) . str_repeat('•', 10) . substr($val, -4)]);
                                                }
                                                return __('Found in Google Cloud Console → Credentials → OAuth 2.0 Client IDs.');
                                            }),

                                        TextInput::make('google_calendar_client_secret')
                                            ->label(__('Client Secret'))
                                            ->password()
                                            ->revealable()
                                            ->placeholder(__('Leave blank to keep existing'))
                                            ->helperText(function () {
                                                $val = Setting::get('google_calendar_client_secret') ?: env('GOOGLE_CALENDAR_CLIENT_SECRET') ?: env('GOOGLE_CLIENT_SECRET');
                                                if ($val) {
                                                    return __('Configured — leave blank to keep existing.');
                                                }
                                                return __('Found in Google Cloud Console → Credentials → OAuth 2.0 Client IDs. Keep this private.');
                                            }),
                                    ])->columns(2),
                            ]),

                        // ── Email ─────────────────────────────────────────────────────────────
                        Tab::make('Email')
                            ->label(__('Email'))
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Section::make(__('SMTP Configuration'))
                                    ->schema([
                                        TextInput::make('mail_host')
                                            ->label(__('SMTP Host'))
                                            ->placeholder(__('smtp.gmail.com'))
                                            ->helperText(__('Your SMTP server hostname.')),

                                        TextInput::make('mail_port')
                                            ->label(__('SMTP Port'))
                                            ->numeric()
                                            ->placeholder(__('587'))
                                            ->helperText(__('Common: 587 (TLS), 465 (SSL), 25 (none).')),

                                        Select::make('mail_encryption')
                                            ->label(__('Encryption'))
                                            ->options([
                                                'tls' => 'TLS',
                                                'ssl' => 'SSL',
                                                ''    => 'None',
                                            ])
                                            ->default('tls'),

                                        TextInput::make('mail_username')
                                            ->label(__('SMTP Username'))
                                            ->placeholder(__('you@example.com')),

                                        TextInput::make('mail_password')
                                            ->label(__('SMTP Password'))
                                            ->password()
                                            ->revealable()
                                            ->placeholder(__('Your SMTP password or app password'))
                                            ->helperText(function () {
                                                $val = Setting::get('mail_password') ?: env('MAIL_PASSWORD');
                                                if ($val && strlen($val) > 4) {
                                                    return __('Configured: :masked — leave blank to keep existing', ['masked' => str_repeat('•', 12) . substr($val, -4)]);
                                                }
                                                return __('App password recommended for Gmail/Outlook.');
                                            }),

                                        TextInput::make('mail_from_address')
                                            ->label(__('From Address'))
                                            ->email()
                                            ->placeholder(__('noreply@example.com')),

                                        TextInput::make('mail_from_name')
                                            ->label(__('From Name'))
                                            ->placeholder(__('Slotara')),
                                    ])->columns(2),

                                Section::make(__('Contact & Notifications'))
                                    ->schema([
                                        TextInput::make('contact_email')
                                            ->label(__('Contact Notification Email'))
                                            ->email()
                                            ->placeholder(__('admin@example.com'))
                                            ->helperText(__('Incoming contact form submissions are forwarded to this address.')),
                                    ])->columns(1),

                                Section::make(__('Send a Test Email'))
                                    ->schema([
                                        TextInput::make('test_email_to')
                                            ->label(__('Recipient'))
                                            ->email()
                                            ->placeholder(__('test@example.com')),

                                        Textarea::make('test_email_message')
                                            ->label(__('Message'))
                                            ->rows(2)
                                            ->default('This is a test email from Texora.'),

                                        Action::make('sendTestEmail')
                                            ->label(__('Send Test Email'))
                                            ->icon('heroicon-o-paper-airplane')
                                            ->action('sendTestEmail')
                                            ->color('success')
                                            ->requiresConfirmation()
                                            ->modalHeading(__('Send a test email?'))
                                            ->modalSubmitActionLabel(__('Send')),
                                    ])->columns(1),
                            ]),

                        // ── System ────────────────────────────────────────────────────────────
                        Tab::make('System')
                            ->label(__('System'))
                            ->icon('heroicon-o-server')
                            ->schema([
                                Section::make(__('Maintenance'))
                                    ->schema([
                                        Toggle::make('maintenance_mode')
                                            ->label(__('Maintenance Mode'))
                                            ->helperText(__('Prevents all public access while enabled.')),

                                        Textarea::make('maintenance_message')
                                            ->label(__('Maintenance Message'))
                                            ->rows(2)
                                            ->visible(fn (Get $get) => $get('maintenance_mode'))
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Section::make(__('Documentation'))
                                    ->schema([
                                        Toggle::make('docs_enabled')
                                            ->label(__('Enable In-App Documentation'))
                                            ->helperText(__('When off, /documentation returns 404. Useful for white-label deployments.'))
                                            ->default(true),
                                    ]),

                                Section::make(__('Localisation'))
                                    ->schema([
                                        Select::make('timezone')
                                            ->label(__('Timezone'))
                                            ->searchable()
                                            ->options(function () {
                                                return collect(timezone_identifiers_list())
                                                    ->mapWithKeys(function (string $tz) {
                                                        try {
                                                            $dt     = new \DateTime('now', new \DateTimeZone($tz));
                                                            $offset = $dt->getOffset();
                                                            $hours  = intdiv($offset, 3600);
                                                            $mins   = abs(intdiv($offset % 3600, 60));

                                                            $utc = $offset === 0
                                                                ? '(UTC+0)'
                                                                : sprintf('(UTC%s%d%s)', $offset > 0 ? '+' : '-', abs($hours), $mins ? ":{$mins}" : '');

                                                            return [$tz => "{$tz} {$utc}"];
                                                        } catch (\Exception) {
                                                            return [$tz => $tz];
                                                        }
                                                    })->all();
                                            })
                                            ->default(config('app.timezone', 'UTC'))
                                            ->required(),

                                        Select::make('date_format')
                                            ->label(__('Date Format'))
                                            ->options([
                                                'Y-m-d'       => '2025-01-15 (Y-m-d)',
                                                'm/d/Y'       => '01/15/2025 (m/d/Y)',
                                                'd/m/Y'       => '15/01/2025 (d/m/Y)',
                                                'd-m-Y'       => '15-01-2025 (d-m-Y)',
                                                'M d, Y'      => 'Jan 15, 2025',
                                                'F d, Y'      => 'January 15, 2025',
                                                'D, M d, Y'   => 'Mon, Jan 15, 2025',
                                                'l, F d, Y'   => 'Monday, January 15, 2025',
                                            ])
                                            ->default('Y-m-d')
                                            ->required(),

                                        Select::make('time_format')
                                            ->label(__('Time Format'))
                                            ->options([
                                                'H:i'   => '14:30 (24-hour)',
                                                'h:i A' => '02:30 PM (12-hour)',
                                                'g:i A' => '2:30 PM (12-hour, no leading zero)',
                                            ])
                                            ->default('H:i')
                                            ->required(),
                                    ])->columns(2),

                                // ── Language Management ───────────────────────────────
                                Section::make(__('Language Management'))
                                    ->description(__('Choose which languages are available across the platform. At least one language must be enabled. English is always included as a fallback.'))
                                    ->collapsible()
                                    ->schema([
                                        \Filament\Forms\Components\CheckboxList::make('enabled_languages')
                                            ->label(__('Enabled Languages'))
                                            ->options([
                                                'en' => '🇬🇧 English',
                                                'ro' => '🇷🇴 Romanian (Română)',
                                                'es' => '🇪🇸 Spanish (Español)',
                                                'de' => '🇩🇪 German (Deutsch)',
                                                'fr' => '🇫🇷 French (Français)',
                                                'ar' => '🇸🇦 Arabic (العربية)',
                                                'ru' => '🇷🇺 Russian (Русский)',
                                                'zh' => '🇨🇳 Chinese (中文)',
                                                'hi' => '🇮🇳 Hindi (हिन्दी)',

                                            ])
                                            ->columns(2)
                                            ->rule(fn () => function (string $attribute, $value, $fail) {
                                                if (empty($value)) {
                                                    $fail(__('At least one language must be enabled.'));
                                                }
                                            })
                                            ->helperText(__('Selected languages appear in the language switcher for all users.')),
                                    ]),

                                // ── Business Registration Options ─────────────────────
                                Section::make(__('Business Registration Options'))
                                    ->description(__('Control which timezones and currencies business owners can choose during setup. Leave empty to allow all.'))
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Select::make('allowed_timezones')
                                            ->label(__('Allowed Timezones'))
                                            ->multiple()
                                            ->searchable()
                                            ->options(fn () => collect(\DateTimeZone::listIdentifiers())
                                                ->mapWithKeys(fn ($tz) => [$tz => $tz])
                                                ->all())
                                            ->helperText(__('Leave empty to show all ~500 PHP timezone identifiers.')),

                                        Select::make('allowed_currencies')
                                            ->label(__('Allowed Currencies'))
                                            ->multiple()
                                            ->searchable()
                                            ->options(fn () => LocalisationOptions::allCurrencies())
                                            ->helperText(__('Leave empty to use the built-in default currency list.')),
                                    ])->columns(1),
                            ]),

                        // ── Security ──────────────────────────────────────────────────────────
                        Tab::make('Security')
                            ->label(__('Security'))
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('session_timeout')
                                            ->label(__('Session Timeout (minutes)'))
                                            ->numeric()
                                            ->default(120)
                                            ->helperText(__('Inactive sessions expire after this many minutes.')),

                                        TextInput::make('max_login_attempts')
                                            ->label(__('Max Login Attempts'))
                                            ->numeric()
                                            ->default(5)
                                            ->helperText(__('Failed attempts before the account is temporarily locked.')),

                                        TextInput::make('lockout_duration')
                                            ->label(__('Lockout Duration (minutes)'))
                                            ->numeric()
                                            ->default(30)
                                            ->helperText(__('How long the account stays locked after max attempts.')),

                                        Toggle::make('force_https')
                                            ->label(__('Force HTTPS'))
                                            ->helperText(__('Redirect all HTTP traffic to HTTPS.')),
                                    ])->columns(2),

                                Section::make(__('Google reCAPTCHA v3'))
                                    ->schema([
                                        Toggle::make('google_recaptcha_enabled')
                                            ->label(__('Enable reCAPTCHA'))
                                            ->helperText(__('When enabled, reCAPTCHA v3 is active on: Login (/login) · Register (/register) · Forgot Password (/forgot-password) · Contact (/contact). Submissions scoring below 0.5 are rejected as likely bots.'))
                                            ->columnSpanFull(),

                                        TextInput::make('google_recaptcha_site_key')
                                            ->label(__('Site Key (v3)'))
                                            ->placeholder(__('6Lc...'))
                                            ->helperText(__('Public key — injected into the page script on protected forms.')),

                                        TextInput::make('google_recaptcha_secret_key')
                                            ->label(__('Secret Key (v3)'))
                                            ->password()
                                            ->revealable()
                                            ->placeholder(__('6Lc...'))
                                            ->helperText(function () {
                                                $val = Setting::get('google_recaptcha_secret_key');
                                                if ($val && strlen($val) > 4) {
                                                    return __('Configured: :masked — leave blank to keep existing', ['masked' => str_repeat('•', 12) . substr($val, -4)]);
                                                }
                                                return __('Private key — used server-side to verify the token score. Never expose this publicly.');
                                            }),
                                    ])->columns(2),
                            ]),

                        // ── Filesystem ────────────────────────────────────────────────────────
                        Tab::make('Filesystem')
                            ->label(__('Filesystem'))
                            ->icon('heroicon-o-circle-stack')
                            ->schema([
                                Section::make(__('Storage Disk'))
                                    ->schema([
                                        Select::make('filesystem_disk')
                                            ->label(__('Default Disk'))
                                            ->options([
                                                'local'  => 'Local',
                                                'public' => 'Public',
                                                's3'     => 'Amazon S3',
                                            ])
                                            ->required()
                                            ->reactive()
                                            ->helperText(__('Where uploaded files are stored.')),
                                    ])->columns(1),

                                Section::make(__('Amazon S3'))
                                    ->visible(fn (Get $get) => $get('filesystem_disk') === 's3')
                                    ->schema([
                                        TextInput::make('s3_access_key_id')
                                            ->label(__('Access Key ID'))
                                            ->required(fn (Get $get) => $get('filesystem_disk') === 's3'),

                                        TextInput::make('s3_secret_access_key')
                                            ->label(__('Secret Access Key'))
                                            ->password()
                                            ->revealable()
                                            ->required(fn (Get $get) => $get('filesystem_disk') === 's3' && ! (Setting::get('s3_secret_access_key') ?: env('S3_SECRET_ACCESS_KEY')))
                                            ->helperText(function () {
                                                $val = Setting::get('s3_secret_access_key') ?: env('S3_SECRET_ACCESS_KEY');
                                                if ($val && strlen($val) > 4) {
                                                    return __('Configured: :masked — leave blank to keep existing', ['masked' => str_repeat('•', 12) . substr($val, -4)]);
                                                }
                                                return __('Your AWS secret access key.');
                                            }),

                                        TextInput::make('s3_default_region')
                                            ->label(__('Region'))
                                            ->placeholder(__('us-east-1'))
                                            ->required(fn (Get $get) => $get('filesystem_disk') === 's3'),

                                        TextInput::make('s3_bucket')
                                            ->label(__('Bucket Name'))
                                            ->required(fn (Get $get) => $get('filesystem_disk') === 's3'),

                                        TextInput::make('s3_endpoint')
                                            ->label(__('Endpoint URL'))
                                            ->placeholder(__('https://s3.us-east-1.amazonaws.com'))
                                            ->url(),

                                        TextInput::make('s3_url')
                                            ->label(__('Public URL'))
                                            ->placeholder(__('https://your-bucket.s3.amazonaws.com'))
                                            ->url()
                                            ->helperText(__('Base URL for serving public files.')),

                                        Toggle::make('s3_use_path_style_endpoint')
                                            ->label(__('Use Path-Style Endpoint'))
                                            ->helperText(__('Required for some S3-compatible providers.')),
                                    ])->columns(2),
                            ]),

                        // ── Payments (Stripe) ─────────────────────────────────────────────────
                        Tab::make('Payments')
                            ->label(__('Payments'))
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Section::make(__('Stripe API Keys'))
                                    ->description(fn () => __('Get these from Stripe Dashboard → Developers → API keys. Use test keys during development, live keys in production. Your webhook endpoint:') . ' ' . url('/stripe/webhook'))
                                    ->schema([
                                        TextInput::make('stripe_key')
                                            ->label(__('Publishable Key'))
                                            ->password()
                                            ->revealable()
                                            ->placeholder(__('pk_live_...'))
                                            ->helperText(function () {
                                                $val = Setting::get('stripe_key') ?: env('STRIPE_KEY');
                                                if ($val && strlen($val) > 8) {
                                                    $masked = substr($val, 0, 8) . str_repeat('•', 8) . substr($val, -4);
                                                    return __('Configured: :masked — leave blank to keep existing', ['masked' => $masked]);
                                                }
                                                return __('Starts with pk_test_ or pk_live_. Safe to expose publicly.');
                                            })
                                            ->columnSpanFull(),

                                        TextInput::make('stripe_secret')
                                            ->label(__('Secret Key'))
                                            ->password()
                                            ->revealable()
                                            ->placeholder(__('sk_live_...'))
                                            ->helperText(function () {
                                                $val = Setting::get('stripe_secret') ?: env('STRIPE_SECRET');
                                                if ($val && strlen($val) > 8) {
                                                    $masked = substr($val, 0, 8) . str_repeat('•', 8) . substr($val, -4);
                                                    return __('Configured: :masked — leave blank to keep existing', ['masked' => $masked]);
                                                }
                                                return __('Starts with sk_test_ or sk_live_. Keep this private — never expose it.');
                                            })
                                            ->columnSpanFull(),

                                        TextInput::make('stripe_webhook_secret')
                                            ->label(__('Webhook Signing Secret'))
                                            ->password()
                                            ->revealable()
                                            ->placeholder(__('whsec_...'))
                                            ->helperText(function () {
                                                $val = Setting::get('stripe_webhook_secret') ?: env('STRIPE_WEBHOOK_SECRET');
                                                if ($val && strlen($val) > 8) {
                                                    $masked = substr($val, 0, 8) . str_repeat('•', 8) . substr($val, -4);
                                                    return __('Configured: :masked — leave blank to keep existing', ['masked' => $masked]);
                                                }
                                                return __('From Stripe Dashboard → Developers → Webhooks → signing secret.');
                                            })
                                            ->columnSpanFull(),

                                        TextInput::make('stripe_portal_config_id')
                                            ->label(__('Billing Portal Configuration ID (optional)'))
                                            ->placeholder(__('bpc_...'))
                                            ->helperText(__('From Stripe Dashboard → Billing → Customer portal → Configuration ID. Only needed if you use multiple portal configurations.'))
                                            ->columnSpanFull(),
                                    ])->columns(2),

                            ]),

                        // ── SEO ──────────────────────────────────────────────────────────────
                        Tab::make('SEO')
                            ->label(__('SEO'))
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make(__('Basic SEO'))
                                    ->schema([
                                        Textarea::make('site_description')
                                            ->label(__('Site Description'))
                                            ->rows(3)
                                            ->helperText(__('Shown in search results and social previews. Keep under 160 characters.'))
                                            ->columnSpanFull(),

                                        TextInput::make('site_keywords')
                                            ->label(__('Meta Keywords'))
                                            ->placeholder(__('booking, appointments, scheduling'))
                                            ->helperText(__('Comma-separated keywords.'))
                                            ->columnSpanFull(),

                                        TextInput::make('site_author')
                                            ->label(__('Site Author'))
                                            ->placeholder(__('Your Company Name')),

                                        Select::make('meta_robots')
                                            ->label(__('Meta Robots'))
                                            ->options([
                                                'index, follow'     => 'Index + Follow (default — allow all)',
                                                'noindex, follow'   => 'NoIndex + Follow (hide from search)',
                                                'index, nofollow'   => 'Index + NoFollow (don\'t follow links)',
                                                'noindex, nofollow' => 'NoIndex + NoFollow (block everything)',
                                            ])
                                            ->default('index, follow'),
                                    ])->columns(2),

                                Section::make(__('Analytics & Tracking'))
                                    ->description(__('Paste your IDs below. Scripts are injected automatically on every frontend page.'))
                                    ->schema([
                                        TextInput::make('google_analytics_id')
                                            ->label(__('Google Analytics ID'))
                                            ->placeholder(__('G-XXXXXXXXXX'))
                                            ->helperText(__('From Google Analytics → Admin → Property Settings.')),

                                        TextInput::make('google_tag_manager_id')
                                            ->label(__('Google Tag Manager ID'))
                                            ->placeholder(__('GTM-XXXXXXX'))
                                            ->helperText(__('From Google Tag Manager → Container ID.')),

                                        TextInput::make('facebook_pixel_id')
                                            ->label(__('Facebook Pixel ID'))
                                            ->placeholder(__('123456789012345'))
                                            ->helperText(__('From Facebook Business Manager → Events Manager.')),

                                        TextInput::make('social_twitter')
                                            ->label(__('Twitter / X Handle'))
                                            ->placeholder(__('YourHandle'))
                                            ->prefix('@')
                                            ->helperText(__('Without the @ symbol. Used in Twitter Card meta tags.')),
                                    ])->columns(2),

                                Section::make(__('Robots.txt'))
                                    ->description(__('Controls how search engine crawlers access your site. Changes are saved directly to public/robots.txt.'))
                                    ->schema([
                                        Textarea::make('robots_txt_content')
                                            ->label(__('robots.txt content'))
                                            ->rows(10)
                                            ->helperText(__('Use "Disallow: /" to block all crawlers, or leave "Disallow:" empty to allow all.'))
                                            ->columnSpanFull(),
                                    ])->columns(1),
                            ]),

                        // ── Password Policy ───────────────────────────────────────────────────
                        Tab::make('Password Policy')
                            ->label(__('Password Policy'))
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make(__('Password Requirements'))
                                    ->schema([
                                        TextInput::make('pp_min_length')
                                            ->label(__('Minimum Length'))
                                            ->numeric()
                                            ->default(8)
                                            ->minValue(4)
                                            ->required(),
                                        Toggle::make('pp_require_uppercase')
                                            ->label(__('Require Uppercase')),
                                        Toggle::make('pp_require_lowercase')
                                            ->label(__('Require Lowercase')),
                                        Toggle::make('pp_require_numbers')
                                            ->label(__('Require Numbers')),
                                        Toggle::make('pp_require_special_chars')
                                            ->label(__('Require Special Characters')),
                                    ])->columns(2),

                                Section::make(__('Account Security'))
                                    ->schema([
                                        TextInput::make('pp_expires_days')
                                            ->label(__('Password Expires After (days)'))
                                            ->numeric()
                                            ->nullable()
                                            ->helperText(__('Leave empty for no expiration.')),
                                        TextInput::make('pp_history_count')
                                            ->label(__('Password History Count'))
                                            ->numeric()
                                            ->default(5)
                                            ->minValue(0)
                                            ->helperText(__('How many previous passwords cannot be reused.')),
                                        TextInput::make('pp_max_login_attempts')
                                            ->label(__('Max Login Attempts'))
                                            ->numeric()
                                            ->default(5)
                                            ->minValue(1)
                                            ->helperText(__('Failed attempts before the account is temporarily locked.')),
                                        TextInput::make('pp_lockout_duration')
                                            ->label(__('Lockout Duration (minutes)'))
                                            ->numeric()
                                            ->default(30)
                                            ->helperText(__('How long the account stays locked after max failed attempts.')),
                                    ])->columns(2),
                            ]),

                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        if (DemoModeHelper::isEnabled()) {
            Notification::make()->title(__('Demo Mode'))->body(DemoModeHelper::getRestrictedMessage())->warning()->send();

            return;
        }

        $originalConfig = config('activitylog.enabled', true);

        try {
            $data = $this->form->getState();

            config(['activitylog.enabled' => false]);

            DB::transaction(function () use ($data, $originalConfig) {
                $excludedFields = ['test_email_to', 'test_email_message', 'test_sms_to'];

                // Write-only secret fields — skip DB update when left blank (keep existing value)
                foreach (['mail_password', 's3_secret_access_key', 'stripe_key', 'stripe_secret', 'stripe_webhook_secret', 'google_recaptcha_secret_key', 'twilio_auth_token', 'google_calendar_client_secret'] as $field) {
                    if (empty($data[$field])) {
                        $excludedFields[] = $field;
                    }
                }

                // Password Policy fields — save to PasswordPolicy model, not the settings table
                $policyFields = [];
                foreach ($data as $key => $value) {
                    if (str_starts_with($key, 'pp_')) {
                        $policyFields[substr($key, 3)] = $value;
                        $excludedFields[] = $key;
                    }
                }
                if (! empty($policyFields)) {
                    config(['activitylog.enabled' => false]);
                    $policy = PasswordPolicy::first();
                    if ($policy) {
                        $policy->update($policyFields);
                    } else {
                        PasswordPolicy::create(array_merge([
                            'name'       => 'Default Policy',
                            'is_default' => true,
                        ], $policyFields));
                    }
                    config(['activitylog.enabled' => $originalConfig]);
                }

                foreach ($data as $key => $value) {
                    if (in_array($key, $excludedFields)) {
                        continue;
                    }

                    if (is_bool($value)) {
                        $value = $value ? '1' : '0';
                    } elseif (is_array($value)) {
                        $value = implode(',', $value);
                    }

                    DB::table('settings')->updateOrInsert(
                        ['key' => $key],
                        ['value' => $value, 'group' => 'general', 'updated_at' => now()]
                    );

                    Cache::forget("setting.{$key}");
                }
            });

            config(['activitylog.enabled' => $originalConfig]);

            if (isset($data['site_name'])) {
                config(['app.name' => $data['site_name']]);
            }

            if (isset($data['timezone'])) {
                config(['app.timezone' => $data['timezone']]);
            }

            // Apply Stripe keys to runtime config immediately (only when a new value was provided)
            if (!empty($data['stripe_key']))            config(['services.stripe.key'            => $data['stripe_key']]);
            if (!empty($data['stripe_secret']))         config(['services.stripe.secret'         => $data['stripe_secret']]);
            if (!empty($data['stripe_webhook_secret'])) config(['services.stripe.webhook_secret' => $data['stripe_webhook_secret']]);

            $maintenanceMode = $data['maintenance_mode'] ?? false;
            cache()->put('maintenance_mode', $maintenanceMode);

            // Sync password-policy fields if present
            try {
                $policyData = [];

                if (isset($data['max_login_attempts'])) {
                    $policyData['max_login_attempts'] = (int) $data['max_login_attempts'];
                }
                if (isset($data['lockout_duration'])) {
                    $policyData['lockout_duration'] = (int) $data['lockout_duration'];
                }

                if (! empty($policyData)) {
                    config(['activitylog.enabled' => false]);
                    $policy = PasswordPolicy::first();

                    if ($policy) {
                        $policy->update($policyData);
                    } else {
                        PasswordPolicy::create(array_merge([
                            'name'                 => 'Default Policy',
                            'min_length'           => 8,
                            'require_uppercase'    => false,
                            'require_lowercase'    => false,
                            'require_numbers'      => false,
                            'require_special_chars' => false,
                            'expires_days'         => null,
                            'history_count'        => 5,
                            'is_default'           => true,
                        ], $policyData));
                    }

                    config(['activitylog.enabled' => $originalConfig]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to sync PasswordPolicy: ' . $e->getMessage());
            }

            // Sync .env for env-backed settings
            $envUpdates = [];

            if (isset($data['timezone'])) {
                $envUpdates['TIME_ZONE'] = $data['timezone'];
            }

            if (isset($data['filesystem_disk'])) {
                $envUpdates['FILESYSTEM_DISK'] = $data['filesystem_disk'];
            }

            // Stripe keys — write to .env so they take effect immediately after cache clear
            foreach (['stripe_key' => 'STRIPE_KEY', 'stripe_secret' => 'STRIPE_SECRET', 'stripe_webhook_secret' => 'STRIPE_WEBHOOK_SECRET'] as $field => $envKey) {
                if (isset($data[$field]) && $data[$field] !== '') {
                    $envUpdates[$envKey] = $data[$field];
                }
            }

            // Twilio — write account SID, from number, and auth token to .env
            foreach (['twilio_account_sid' => 'TWILIO_ACCOUNT_SID', 'twilio_from_number' => 'TWILIO_FROM_NUMBER'] as $field => $envKey) {
                if (isset($data[$field]) && $data[$field] !== '') {
                    $envUpdates[$envKey] = $data[$field];
                }
            }
            if (isset($data['twilio_auth_token']) && $data['twilio_auth_token'] !== '') {
                $envUpdates['TWILIO_AUTH_TOKEN'] = $data['twilio_auth_token'];
            }

            // Google Calendar OAuth credentials
            if (isset($data['google_calendar_client_id']) && $data['google_calendar_client_id'] !== '') {
                $envUpdates['GOOGLE_CALENDAR_CLIENT_ID'] = $data['google_calendar_client_id'];
            }
            if (isset($data['google_calendar_client_secret']) && $data['google_calendar_client_secret'] !== '') {
                $envUpdates['GOOGLE_CALENDAR_CLIENT_SECRET'] = $data['google_calendar_client_secret'];
            }

            if (isset($data['filesystem_disk']) && $data['filesystem_disk'] === 's3') {
                foreach (['s3_access_key_id' => 'S3_ACCESS_KEY_ID', 's3_secret_access_key' => 'S3_SECRET_ACCESS_KEY', 's3_default_region' => 'S3_DEFAULT_REGION', 's3_bucket' => 'S3_BUCKET', 's3_endpoint' => 'S3_ENDPOINT', 's3_url' => 'S3_URL'] as $field => $envKey) {
                    if (isset($data[$field])) {
                        $envUpdates[$envKey] = $data[$field];
                    }
                }

                if (isset($data['s3_use_path_style_endpoint'])) {
                    $envUpdates['S3_USE_PATH_STYLE_ENDPOINT'] = $data['s3_use_path_style_endpoint'] ? 'true' : 'false';
                }
            }

            if (! empty($envUpdates)) {
                try {
                    $this->updateEnv($envUpdates);
                } catch (\Exception $e) {
                    Log::error('Failed to update .env: ' . $e->getMessage());
                }
            }

            // Update robots.txt
            if (isset($data['robots_txt_content'])) {
                try {
                    file_put_contents(public_path('robots.txt'), $data['robots_txt_content']);
                } catch (\Exception $e) {
                    Log::error('Failed to write robots.txt: ' . $e->getMessage());
                }
            }

            config(['activitylog.enabled' => $originalConfig]);

            Notification::make()
                ->title(__('Settings saved'))
                ->success()
                ->send();

        } catch (ValidationException $e) {
            config(['activitylog.enabled' => $originalConfig]);
            throw $e;
        } catch (\Throwable $e) {
            config(['activitylog.enabled' => $originalConfig]);

            Log::error('Settings save failed: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title(__('Failed to save settings'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function sendTestEmail(): void
    {
        try {
            $data    = $this->data;
            $to      = $data['test_email_to'] ?? null;
            $message = $data['test_email_message'] ?? 'This is a test email from Texora.';

            if (! $to) {
                Notification::make()->title(__('Please enter a recipient email address'))->warning()->send();

                return;
            }

            config()->set('mail.mailers.smtp', [
                'transport'  => 'smtp',
                'host'       => $data['mail_host'] ?? config('mail.mailers.smtp.host'),
                'port'       => $data['mail_port'] ?? config('mail.mailers.smtp.port'),
                'username'   => $data['mail_username'] ?? config('mail.mailers.smtp.username'),
                'password'   => $data['mail_password'] ?? config('mail.mailers.smtp.password'),
                'encryption' => $data['mail_encryption'] ?? config('mail.mailers.smtp.encryption'),
            ]);

            config()->set('mail.from.address', $data['mail_from_address'] ?? config('mail.from.address'));
            config()->set('mail.from.name', $data['mail_from_name'] ?? config('mail.from.name'));

            EmailTemplateService::sendWithLayoutFallback(
                to: $to,
                subjectFallback: 'Test Email from Texora',
                bodyFallback: '<p>{TEST_MESSAGE}</p>',
                placeholders: [
                    'TEST_MESSAGE'    => nl2br(e($message)),
                    'RECIPIENT_EMAIL' => $to,
                ],
                templateSlug: 'settings-test-email'
            );

            Notification::make()->title(__('Test email sent to :email', ['email' => $to]))->success()->send();
        } catch (Exception $e) {
            Notification::make()->title(__('Failed to send test email'))->body($e->getMessage())->danger()->send();
        }
    }

    public function sendTestSms(): void
    {
        try {
            $to = $this->data['test_sms_to'] ?? null;

            if (! $to) {
                Notification::make()->title(__('Please enter a recipient phone number'))->warning()->send();

                return;
            }

            $accountSid = Setting::get('twilio_account_sid', env('TWILIO_ACCOUNT_SID'));
            $authToken  = Setting::get('twilio_auth_token', env('TWILIO_AUTH_TOKEN'));
            $from       = Setting::get('twilio_from_number', env('TWILIO_FROM_NUMBER'));

            if (! $accountSid || ! $authToken || ! $from) {
                Notification::make()
                    ->title(__('Twilio is not configured. Enter Account SID, Auth Token, and From Number first.'))
                    ->warning()
                    ->send();

                return;
            }

            Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'To'   => $to,
                    'From' => $from,
                    'Body' => __('Test SMS from :name.', ['name' => Setting::get('site_name', config('app.name'))]),
                ])
                ->throw();

            Notification::make()->title(__('Test SMS sent to :phone', ['phone' => $to]))->success()->send();
        } catch (Exception $e) {
            Notification::make()->title(__('Failed to send test SMS'))->body($e->getMessage())->danger()->send();
        }
    }

    protected function updateEnv(array $data): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return;
        }

        $content = File::get($envPath);

        foreach ($data as $key => $value) {
            if (preg_match("/^#?\s*{$key}=.*/m", $content)) {
                $content = preg_replace("/^#?\s*{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $content);
    }

    protected function quoteEnvValue(string $value): string
    {
        $escaped = str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '', ''], $value);

        return '"' . $escaped . '"';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function getTitle(): string
    {
        return __('Settings');
    }
}
