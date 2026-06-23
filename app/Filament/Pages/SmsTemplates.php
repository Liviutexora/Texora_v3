<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SmsTemplates extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 115;

    protected static ?string $slug = 'sms-templates';

    protected string $view = 'filament.pages.sms-templates';

    public ?array $data = [];

    const DEFAULTS = [
        'sms_template_confirmation' => 'Hi {CLIENT_NAME}, your booking at {TENANT_NAME} is confirmed. {SERVICE_NAME} on {BOOKING_DATE} at {BOOKING_TIME}. Ref {BOOKING_ID}. Cancel: {CANCEL_URL}',
        'sms_template_reminder'     => 'Reminder: {SERVICE_NAME} tomorrow ({BOOKING_DATE}) at {BOOKING_TIME} with {TENANT_NAME}. Cancel: {CANCEL_URL}',
        'sms_template_cancellation' => 'Your booking for {SERVICE_NAME} on {BOOKING_DATE} at {BOOKING_TIME} with {TENANT_NAME} has been cancelled. Ref {BOOKING_ID}.',
        'sms_template_rescheduled'  => 'Your booking at {TENANT_NAME} has been rescheduled to {BOOKING_DATE} at {BOOKING_TIME} ({SERVICE_NAME}). Ref {BOOKING_ID}.',
    ];

    public function mount(): void
    {
        $this->form->fill([
            'sms_template_confirmation' => Setting::get('sms_template_confirmation', self::DEFAULTS['sms_template_confirmation']),
            'sms_template_reminder'     => Setting::get('sms_template_reminder',     self::DEFAULTS['sms_template_reminder']),
            'sms_template_cancellation' => Setting::get('sms_template_cancellation', self::DEFAULTS['sms_template_cancellation']),
            'sms_template_rescheduled'  => Setting::get('sms_template_rescheduled',  self::DEFAULTS['sms_template_rescheduled']),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $placeholders = __('Available placeholders: {CLIENT_NAME}, {SERVICE_NAME}, {BOOKING_DATE}, {BOOKING_TIME}, {BOOKING_ID}, {PROVIDER_NAME}, {CANCEL_URL}, {TENANT_NAME}');

        return $schema
            ->components([
                Section::make(__('SMS Message Templates'))
                    ->description(__('Customize the SMS messages sent for each booking event. Wrap variable names in curly braces exactly as shown.'))
                    ->schema([
                        Textarea::make('sms_template_confirmation')
                            ->label(__('Booking Confirmation'))
                            ->rows(3)
                            ->required()
                            ->helperText($placeholders)
                            ->columnSpanFull(),

                        Textarea::make('sms_template_reminder')
                            ->label(__('Appointment Reminder'))
                            ->rows(3)
                            ->required()
                            ->helperText($placeholders)
                            ->columnSpanFull(),

                        Textarea::make('sms_template_cancellation')
                            ->label(__('Cancellation Notice'))
                            ->rows(3)
                            ->required()
                            ->helperText($placeholders)
                            ->columnSpanFull(),

                        Textarea::make('sms_template_rescheduled')
                            ->label(__('Reschedule Confirmation'))
                            ->rows(3)
                            ->required()
                            ->helperText($placeholders)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (array_keys(self::DEFAULTS) as $key) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $data[$key] ?? self::DEFAULTS[$key], 'group' => 'sms', 'updated_at' => now()]
            );
            Cache::forget("setting.{$key}");
        }

        Notification::make()->title(__('SMS templates saved'))->success()->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Communication');
    }

    public static function getNavigationLabel(): string
    {
        return __('SMS Templates');
    }

    public function getTitle(): string
    {
        return __('SMS Templates');
    }
}
