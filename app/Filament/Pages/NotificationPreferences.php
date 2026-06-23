<?php

namespace App\Filament\Pages;

use App\Models\NotificationPreference;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Pages\Page;
use App\Filament\Concerns\HasPagePermissions;
use App\Helpers\DemoModeHelper;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationPreferences extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPagePermissions;

    protected static ?string $slug = 'notification-preferences';
    protected string $view = 'filament.pages.notification-preferences';
    public array $data = [];

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell';
    protected static ?string $title = null;
    protected static ?int $navigationSort = 120;
    protected static ?string $navigationLabel = 'Notification Preferences';
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    private const ADMIN_NOTIFICATIONS = [
        'new_registration'        => 'New Business Registration',
        'new_contact_enquiry'     => 'New Contact Form Submission',
        'payment_failed'          => 'Tenant Payment Failed',
        'subscription_cancelled'  => 'Tenant Subscription Cancelled',
    ];

    private const SYSTEM_EMAILS = [
        'welcome_email'             => 'Welcome Email to New Registrants',
        'reset_password_confirmation'=> 'Password Reset Confirmation to Users',
        'booking_confirmation'      => 'Booking Confirmation to Clients',
        'booking_cancellation'      => 'Booking Cancellation Notice to Clients',
        'booking_reminder'          => 'Appointment Reminder to Clients',
        'booking_rescheduled'       => 'Reschedule Confirmation to Clients',
        'user_contact_confirmation' => 'Contact Form Confirmation to Submitters',
        'admin_contact_reply'       => 'Contact Reply Email to Customers',
        'provider_new_booking'      => 'New Booking Notification to Providers',
    ];

    private const SYSTEM_SMS = [
        'booking_confirmation' => 'Booking Confirmation SMS to Clients',
        'booking_cancellation' => 'Booking Cancellation SMS to Clients',
        'booking_reminder'     => 'Appointment Reminder SMS to Clients',
        'booking_rescheduled'  => 'Reschedule Confirmation SMS to Clients',
    ];

    public function mount(): void
    {
        $all = array_unique(array_merge(
            array_keys(self::ADMIN_NOTIFICATIONS),
            array_keys(self::SYSTEM_EMAILS),
            array_keys(self::SYSTEM_SMS),
        ));

        $userId = Auth::id();

        $prefs = collect($all)->map(fn ($name) => NotificationPreference::firstOrCreate([
            'user_id'         => $userId,
            'permission_name' => $name,
        ]));

        $this->data = $prefs->mapWithKeys(fn ($pref) => [
            $pref->permission_name => [
                'email'            => (bool) $pref->email,
                'web_notification' => (bool) $pref->web_notification,
                'sms'              => (bool) $pref->sms,
            ],
        ])->toArray();
    }

    public function form(Schema $schema): Schema
    {
        // Admin notification rows (3 columns: label | email | web)
        $adminRows = collect(self::ADMIN_NOTIFICATIONS)
            ->flatMap(fn ($label, $perm) => [
                Placeholder::make($perm)->label(__($label))->columnSpan(1),
                Toggle::make("{$perm}.email")->label(__('Email'))->columnSpan(1),
                Toggle::make("{$perm}.web_notification")->label(__('Web (in-app)'))->columnSpan(1),
            ])->toArray();

        // System email toggle rows (2 columns: label | email on/off)
        $systemRows = collect(self::SYSTEM_EMAILS)
            ->flatMap(fn ($label, $perm) => [
                Placeholder::make("email_{$perm}")->label(__($label))->columnSpan(1),
                Toggle::make("{$perm}.email")->label(__('Enabled'))->columnSpan(1),
            ])->toArray();

        $smsRows = collect(self::SYSTEM_SMS)
            ->flatMap(fn ($label, $perm) => [
                Placeholder::make("sms_{$perm}")->label(__($label))->columnSpan(1),
                Toggle::make("{$perm}.sms")->label(__('Enabled'))->columnSpan(1),
            ])->toArray();

        return $schema
            ->components([
                Section::make(__('My Notifications'))
                    ->description(__('Receive email and/or in-app notifications when these platform events occur.'))
                    ->schema($adminRows)
                    ->columns(3),

                Section::make(__('System Email Settings'))
                    ->description(__('Control which automated emails are sent by the platform. Disabling a type stops all emails of that type from being sent.'))
                    ->schema($systemRows)
                    ->columns(2),

                Section::make(__('System SMS Settings'))
                    ->description(__('Allow client booking SMS when tenants have Twilio configured. At least one super-admin must enable each type (opt-in). Tenants control their own Twilio credentials and per-event toggles.'))
                    ->schema($smsRows)
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        if (DemoModeHelper::isEnabled()) {
            Notification::make()->title(__('Demo Mode'))->body(DemoModeHelper::getRestrictedMessage())->warning()->send();
            return;
        }

        foreach ($this->data as $permissionName => $values) {
            NotificationPreference::updateOrCreate(
                [
                    'user_id'         => Auth::id(),
                    'permission_name' => $permissionName,
                ],
                [
                    'email'            => $values['email'] ?? false,
                    'web_notification' => $values['web_notification'] ?? false,
                    'sms'              => $values['sms'] ?? false,
                ]
            );
        }

        Notification::make()
            ->title(__('Notification preferences updated successfully!'))
            ->success()
            ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Communication');
    }

    public static function getNavigationLabel(): string
    {
        return __('Notification Preferences');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Notification Preferences');
    }
}
