<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Widgets\BookingRateStatsWidget;
use App\Filament\Tenant\Widgets\BookingsByDayOfWeekChart;
use App\Filament\Tenant\Widgets\BookingsTrendChart;
use App\Filament\Tenant\Widgets\QuickActionsWidget;
use App\Filament\Tenant\Widgets\RevenueByServiceChart;
use App\Filament\Tenant\Widgets\TenantStatsOverview;
use App\Filament\Tenant\Widgets\TodayScheduleWidget;
use App\Filament\Tenant\Widgets\UpcomingBookingsWidget;
use App\Support\TenantContext;
use Filament\Pages\Dashboard;

class TenantDashboard extends Dashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static string $routePath = '/';

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    public function getTitle(): string
    {
        $tenant = TenantContext::current();

        return $tenant ? sprintf(__('Welcome, %s'), $tenant->name) : __('Dashboard');
    }

    public function getWidgets(): array
    {
        return [
            QuickActionsWidget::class,       // sort 0 — action buttons at top
            TenantStatsOverview::class,      // sort 1 — 6 stat cards
            TodayScheduleWidget::class,      // sort 2 — today's appointments
            BookingRateStatsWidget::class,   // sort 2 — health rates
            BookingsTrendChart::class,       // sort 3 — 30d/3m/6m trend
            RevenueByServiceChart::class,    // sort 4 — revenue doughnut
            BookingsByDayOfWeekChart::class, // sort 5 — busiest days bar
            UpcomingBookingsWidget::class,   // sort 2 (full-width upcoming table)
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'sm'  => 1,
            'md'  => 2,
            'xl'  => 2,
        ];
    }
}
