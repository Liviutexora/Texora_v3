<?php

namespace App\Filament\Tenant\Pages;

use App\Helpers\DemoModeHelper;
use App\Support\TenantContext;
use App\Support\TenantSmsSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SmsSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 15;

    protected static ?string $slug = 'sms-settings';

    protected string $view = 'filament.tenant.pages.sms-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Communication');
    }

    public static function getNavigationLabel(): string
    {
        return __('SMS');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('SMS Notifications');
    }

    public function mount(): void
    {
        $tenant = TenantContext::current();

        if (! $tenant) {
            return;
        }

        $this->form->fill(TenantSmsSettings::for($tenant->id)->toFormData());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Twilio credentials'))
                    ->description(__('Connect your Twilio account to send SMS to customers. Messages are billed by Twilio to your account.'))
                    ->schema([
                        Toggle::make('sms_enabled')
                            ->label(__('Enable SMS'))
                            ->helperText(__('Master switch for all outbound SMS from this business.')),

                        TextInput::make('twilio_account_sid')
                            ->label(__('Account SID'))
                            ->helperText(__('Found in Twilio Console → Account Info.'))
                            ->placeholder('ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),

                        TextInput::make('twilio_auth_token')
                            ->label(__('Auth Token'))
                            ->helperText(__('Found in Twilio Console → Account Info. Keep this private.'))
                            ->password()
                            ->revealable(),

                        TextInput::make('twilio_from_number')
                            ->label(__('From number'))
                            ->helperText(__('E.164 format, e.g. +15005550006'))
                            ->placeholder('+15005550006'),
                    ]),

                Section::make(__('Message types'))
                    ->description(__('Choose which booking events trigger an SMS. Customers must have a phone number on the booking.'))
                    ->schema([
                        Toggle::make('sms_confirmation')
                            ->label(__('Booking confirmation'))
                            ->helperText(__('Sent when a booking is confirmed.')),

                        Toggle::make('sms_reminder')
                            ->label(__('Appointment reminder'))
                            ->helperText(__('Sent the day before the appointment (same schedule as email reminders).')),

                        Toggle::make('sms_cancellation')
                            ->label(__('Cancellation notice'))
                            ->helperText(__('Sent when a booking is cancelled.')),

                        Toggle::make('sms_rescheduled')
                            ->label(__('Reschedule confirmation'))
                            ->helperText(__('Sent when a booking is rescheduled to a new time.')),
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

        $tenant = TenantContext::current();

        if (! $tenant) {
            return;
        }

        $data = $this->form->getState();

        TenantSmsSettings::for($tenant->id)->save($data);

        Notification::make()->title(__('SMS settings saved'))->success()->send();
    }
}
