<?php

namespace App\Filament\Tenant\Pages;

use App\Helpers\DemoModeHelper;
use App\Models\Provider;
use App\Support\TenantCalendarSettings;
use App\Support\TenantContext;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class CalendarSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'calendar-settings';

    protected string $view = 'filament.tenant.pages.calendar-settings';

    public ?array $data = [];

    public Collection $providers;

    public function mount(): void
    {
        $tenant = TenantContext::current();

        if (! $tenant) {
            $this->providers = collect();

            return;
        }

        $this->form->fill(TenantCalendarSettings::for($tenant->id)->toFormData());
        $this->providers = Provider::query()
            ->where('tenant_id', $tenant->id)
            ->with('user')
            ->orderBy('id')
            ->get();
    }

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Integrations');
    }

    public static function getNavigationLabel(): string
    {
        return __('Google Calendar');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Google Calendar Sync');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Calendar integration'))
                    ->description(__('Push confirmed bookings to each provider\'s Google Calendar. Two-way sync blocks busy times from external events.'))
                    ->schema([
                        Toggle::make('calendar_sync_enabled')
                            ->label(__('Enable Google Calendar sync')),

                        Toggle::make('calendar_two_way_sync')
                            ->label(__('Two-way sync (import busy times)'))
                            ->helperText(__('Creates blocked slots from external calendar events. Schedule: php artisan calendar:sync-busy')),
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

        TenantCalendarSettings::for($tenant->id)->save($this->form->getState());

        Notification::make()->title(__('Calendar settings saved'))->success()->send();
    }
}
