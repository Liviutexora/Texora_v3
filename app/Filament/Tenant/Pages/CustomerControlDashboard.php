<?php

namespace App\Filament\Tenant\Pages;

use Filament\Pages\Page;

class CustomerControlDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'panou-control-clienti';

    protected string $view = 'filament.tenant.pages.customer-control-dashboard';

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    public static function getNavigationLabel(): string
    {
        return __('Panou de control clienți');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Panou de control clienți');
    }
}
