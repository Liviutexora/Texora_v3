<?php

namespace App\Filament\Tenant\Pages;

use App\Models\NotificationPreference;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class NotificationPreferences extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Account';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'notification-preferences';

    protected string $view = 'filament.tenant.pages.notification-preferences';

    public array $data = [];

    /**
     * All super_admin users can access the admin prefs page.
     * Here tenant staff/owners see their own booking notification preferences.
     */
    public static function getNavigationLabel(): string
    {
        return __('Notifications');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Notification Preferences');
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $defaults = [
            'new_booking',
            'booking_cancelled',
        ];

        $userId = Auth::id();

        $prefs = collect($defaults)->map(fn ($name) => NotificationPreference::firstOrCreate([
            'user_id'         => $userId,
            'permission_name' => $name,
        ]));

        $this->data = $prefs->mapWithKeys(fn ($pref) => [
            $pref->permission_name => [
                'email'            => (bool) $pref->email,
                'web_notification' => (bool) $pref->web_notification,
            ],
        ])->toArray();
    }

    public function form(Schema $schema): Schema
    {
        $labels = [
            'new_booking'      => __('New Booking Received'),
            'booking_cancelled' => __('Booking Cancelled by Client'),
        ];

        $rows = collect($this->data)->keys()->flatMap(function ($permissionName) use ($labels) {
            $label = $labels[$permissionName] ?? ucwords(str_replace('_', ' ', $permissionName));

            return [
                Placeholder::make($permissionName)
                    ->label($label)
                    ->columnSpan(1),

                Toggle::make("{$permissionName}.email")
                    ->label(__('Email'))
                    ->columnSpan(1),

                Toggle::make("{$permissionName}.web_notification")
                    ->label(__('Web (in-app)'))
                    ->columnSpan(1),
            ];
        })->toArray();

        return $schema
            ->components([
                Section::make(__('Booking Notification Preferences'))
                    ->description(__('Choose how you want to be notified about booking activity.'))
                    ->schema($rows)
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->data as $permissionName => $values) {
            NotificationPreference::updateOrCreate(
                [
                    'user_id'         => Auth::id(),
                    'permission_name' => $permissionName,
                ],
                [
                    'email'            => $values['email'] ?? false,
                    'web_notification' => $values['web_notification'] ?? false,
                ]
            );
        }

        Notification::make()
            ->title(__('Notification preferences saved!'))
            ->success()
            ->send();
    }
}
