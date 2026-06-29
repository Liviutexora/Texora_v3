<?php

namespace App\Filament\Tenant\Pages;

use Filament\Pages\Page;

class CustomerReturnsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'reveniri-clienti';

    protected string $view = 'filament.tenant.pages.customer-returns-page';

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    public static function getNavigationLabel(): string
    {
        return __('Reminder clienți');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Reminder clienți');
    }
}
