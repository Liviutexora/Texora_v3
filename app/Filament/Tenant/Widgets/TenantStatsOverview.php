<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TenantStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    /** Map ISO 4217 currency code → symbol for display. */
    private static function currencySymbol(string $code): string
    {
        return match (strtoupper($code)) {
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            'INR' => '₹',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'SGD' => 'S$',
            'AED' => 'AED ',
            'JPY' => '¥',
            default => $code . ' ',
        };
    }

    protected function getStats(): array
    {
        $tenantId  = TenantContext::id();
        $tenant    = TenantContext::current();
        $currency  = $tenant?->currency ?? 'INR';
        $symbol    = self::currencySymbol($currency);
        $lastMonth = now()->subMonthNoOverflow();

        $base = fn () => SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId);

        // ── Today ────────────────────────────────────────────────────
        $todayRows = (clone $base)()
            ->whereDate('date', today())
            ->selectRaw("COUNT(*) as total,
                SUM(status = 'confirmed') as confirmed,
                SUM(status = 'pending')   as pending")
            ->first();

        $todayTotal     = (int) ($todayRows->total     ?? 0);
        $todayConfirmed = (int) ($todayRows->confirmed ?? 0);
        $todayPending   = (int) ($todayRows->pending   ?? 0);

        // ── Sparkline: last 7 days in one query ──────────────────────
        $sparkStart = now()->subDays(6)->startOfDay();
        $sparkRows  = (clone $base)()
            ->whereBetween('date', [$sparkStart, now()->endOfDay()])
            ->selectRaw('DATE(date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $last7 = collect(range(6, 0))
            ->map(fn ($d) => (int) ($sparkRows[now()->subDays($d)->toDateString()] ?? 0))
            ->toArray();

        // ── This month vs last month ──────────────────────────────────
        $monthBookings = (clone $base)()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();

        $lastMonthBookings = (clone $base)()
            ->whereMonth('date', $lastMonth->month)
            ->whereYear('date', $lastMonth->year)
            ->count();

        $bookingTrend = $lastMonthBookings > 0
            ? round((($monthBookings - $lastMonthBookings) / $lastMonthBookings) * 100)
            : 0;

        // ── Revenue this month (paid bookings) ────────────────────────
        $monthRevenue = (clone $base)()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('payment_status', 'paid')
            ->sum('amount');

        // ── Pending across all dates ──────────────────────────────────
        $allPending = (clone $base)()->where('status', 'pending')->count();

        // ── Total clients (distinct users who have booked) ────────────
        $totalClients = (clone $base)()
            ->whereNotNull('email')
            ->distinct('email')
            ->count('email');

        $newClientsThisMonth = (clone $base)()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereNotNull('email')
            ->distinct('email')
            ->count('email');

        return [
            Stat::make(__("Today's Bookings"), $todayTotal)
                ->description(sprintf(__('%d confirmed · %d pending'), $todayConfirmed, $todayPending))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->chart($last7)
                ->color('violet'),

            Stat::make(__('This Month'), $monthBookings)
                ->description($bookingTrend >= 0
                    ? sprintf(__('+%d%% vs last month'), $bookingTrend)
                    : sprintf(__('%d%% vs last month'), $bookingTrend))
                ->descriptionIcon($bookingTrend >= 0
                    ? 'heroicon-m-arrow-trending-up'
                    : 'heroicon-m-arrow-trending-down')
                ->color($bookingTrend >= 0 ? 'success' : 'danger'),

            Stat::make(__('Revenue This Month'), $symbol . number_format((float) $monthRevenue, 2))
                ->description(__('Paid bookings only'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(__('Pending Confirmation'), $allPending)
                ->description(__('Across all dates'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('Total Clients'), $totalClients)
                ->description(sprintf(__('+%d new this month'), $newClientsThisMonth))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
