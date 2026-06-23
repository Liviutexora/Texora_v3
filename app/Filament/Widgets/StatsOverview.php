<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasWidgetPermissions;
use App\Models\LoginActivity;
use App\Models\User;
use App\Models\UserSession;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use HasWidgetPermissions;

    protected static ?string $model = LoginActivity::class;

    protected int|string|array $columnSpan = 'full';

    protected function getHeading(): ?string
    {
        return __('System overview');
    }

    protected function getDescription(): ?string
    {
        return __('Users and sessions.');
    }

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $usersInLastMonth = User::where('created_at', '>=', Carbon::now()->subMonth())->count();
        $recentLogins = LoginActivity::where('logged_in_at', '>=', Carbon::now()->subDay())->count();
        $activeSessions = UserSession::where('is_active', true)->count();

        return [
            Stat::make(__('Total Users'), $totalUsers)
                ->description("{$usersInLastMonth} " . __('users in last month'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->url(route('filament.admin.resources.users.index'))
                ->extraAttributes(['class' => 'cursor-pointer']),

            Stat::make(__('Active Sessions'), $activeSessions)
                ->description("{$recentLogins} " . __('logins in last 24h'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->url(route('filament.admin.pages.profile').'#sessions'),
        ];
    }
}
