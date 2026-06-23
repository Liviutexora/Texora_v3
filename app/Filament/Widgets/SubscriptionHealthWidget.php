<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubscriptionHealthWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getHeading(): ?string
    {
        return __('Subscription health');
    }

    protected function getStats(): array
    {
        $trialing  = Tenant::where('stripe_subscription_status', 'trialing')->count();
        $pastDue   = Tenant::where('stripe_subscription_status', 'past_due')->count();
        $suspended = Tenant::where('status', 'suspended')->count();
        $canceled  = Tenant::where('stripe_subscription_status', 'canceled')->count();

        return [
            Stat::make(__('On Trial'), $trialing)
                ->description(__('Stripe trialing period'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('Past Due'), $pastDue)
                ->description(__('Payment failed — still accessible'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($pastDue > 0 ? 'danger' : 'success'),

            Stat::make(__('Suspended'), $suspended)
                ->description(__('No access to booking page'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($suspended > 0 ? 'danger' : 'success'),

            Stat::make(__('Canceled'), $canceled)
                ->description(__('Subscription canceled'))
                ->descriptionIcon('heroicon-m-trash')
                ->color('gray'),
        ];
    }
}
