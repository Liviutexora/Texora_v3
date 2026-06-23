<?php

namespace App\Filament\Tenant\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;

class BookingCalendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    public static function getNavigationGroup(): ?string { return __('Operations'); }

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.tenant.pages.booking-calendar';

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    public static function getNavigationLabel(): string
    {
        return __('Calendar');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Booking Calendar');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list_view')
                ->label(__('List View'))
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(route('filament.tenant.resources.bookings.index')),
        ];
    }
}
