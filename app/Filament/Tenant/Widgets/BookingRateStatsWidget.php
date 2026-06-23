<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingRateStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $tenantId = TenantContext::id();

        $base = fn () => SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);

        $total     = (clone $base)()->count();
        $confirmed = (clone $base)()->whereIn('status', ['confirmed', 'completed'])->count();
        $cancelled = (clone $base)()->where('status', 'cancelled')->count();
        $noShow    = (clone $base)()->where('status', 'no_show')->count();
        $pending   = (clone $base)()->where('status', 'pending')->count();

        $confirmRate = $total > 0 ? round($confirmed / $total * 100) : 0;
        $cancelRate  = $total > 0 ? round($cancelled / $total * 100) : 0;
        $noShowRate  = $total > 0 ? round($noShow  / $total * 100) : 0;

        $upcoming7 = SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [today(), today()->addDays(6)])
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        return [
            Stat::make(__('Bookings This Month'), $total)
                ->description(sprintf(__('%d pending confirmation'), $pending))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('violet'),

            Stat::make(__('Confirm Rate'), "{$confirmRate}%")
                ->description(__('Confirmed + completed'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('Cancellation Rate'), "{$cancelRate}%")
                ->description(__('Cancelled this month'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($cancelRate > 20 ? 'danger' : 'warning'),

            Stat::make(__('No-show Rate'), "{$noShowRate}%")
                ->description(__('Did not attend'))
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($noShowRate > 10 ? 'danger' : 'gray'),

            Stat::make(__('Upcoming (Next 7 Days)'), $upcoming7)
                ->description(__('Pending or confirmed'))
                ->descriptionIcon('heroicon-m-arrow-right-circle')
                ->color('info'),
        ];
    }
}
