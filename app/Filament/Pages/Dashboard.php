<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasPagePermissions;
use App\Filament\Widgets\PlatformStatsWidget;
use App\Filament\Widgets\RecentSignupsWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\SubscriptionHealthWidget;
use App\Filament\Widgets\UsersActivityLineChart;
use App\Filament\Widgets\UsersDoughnutChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    use HasPagePermissions;

    protected static ?int $navigationSort = -2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-c-squares-plus';

    public static function canAccess(): bool
    {
        $user = static::getAuthUser();
        if (! $user) {
            return false;
        }
        if ($user->hasAnyRole(['super_admin'])) {
            return true;
        }

        return $user->can('View:'.class_basename(static::class));
    }

    public function __construct()
    {
        checkAndAssignUserRole();
    }

    public function getWidgets(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $widgets = [
            PlatformStatsWidget::class,
            SubscriptionHealthWidget::class,
            StatsOverview::class,
            UsersActivityLineChart::class,
            UsersDoughnutChart::class,
            RecentSignupsWidget::class,
        ];

        return array_filter($widgets, fn ($w) => $user->hasRole('super_admin') || $w::canView());
    }

    public function getColumns(): int|array
    {
        return [
            'sm' => 4,
            'md' => 4,
            'lg' => 4,
        ];
    }
}
