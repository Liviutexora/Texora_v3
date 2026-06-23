<?php

namespace App\Filament\Widgets;

use App\Models\SlotReservation;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStatsWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        // Active = paying (stripe active) or non-Stripe active tenants
        $activeTenants = Tenant::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('stripe_subscription_status')
                  ->orWhere('stripe_subscription_status', 'active');
            })
            ->count();

        // Trialing = Stripe trialing period only
        $trialTenants = Tenant::where('stripe_subscription_status', 'trialing')->count();

        // MRR: only paying tenants; yearly plans normalised to monthly
        $mrr = Tenant::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('stripe_subscription_status')
                  ->orWhere('stripe_subscription_status', 'active');
            })
            ->whereNotNull('plan_id')
            ->with(['plan.activePrices'])
            ->get()
            ->sum(function ($t) {
                if (! $t->plan) {
                    return 0;
                }
                // Prefer prices table; monthly price or yearly÷12; legacy column as fallback
                $prices  = $t->plan->activePrices;
                $monthly = $prices->firstWhere('billing_cycle', 'monthly');
                if ($monthly) {
                    return (float) $monthly->price;
                }
                $yearly = $prices->firstWhere('billing_cycle', 'yearly');
                if ($yearly) {
                    return (float) $yearly->price / 12;
                }
                // Legacy fallback for plans without prices table entries
                $legacy = (float) ($t->plan->price ?? 0);
                return $t->plan->billing_cycle === 'yearly' ? $legacy / 12 : $legacy;
            });

        $todayBookings = SlotReservation::withoutGlobalScope('tenant')
            ->whereDate('date', today())
            ->count();

        $monthBookings = SlotReservation::withoutGlobalScope('tenant')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();

        return [
            Stat::make(__('Active Tenants'), $activeTenants)
                ->description("{$trialTenants} " . __('on trial'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success'),

            Stat::make('MRR', '$' . number_format($mrr, 2))
                ->description(__('Monthly recurring revenue'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make(__("Today's Bookings"), $todayBookings)
                ->description(__('Across all tenants'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make(__('Bookings This Month'), $monthBookings)
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
        ];
    }
}
