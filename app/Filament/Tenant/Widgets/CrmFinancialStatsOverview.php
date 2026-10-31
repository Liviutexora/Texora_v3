<?php

namespace App\Filament\Tenant\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmFinancialStatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make(__('Revenue from returning clients'), __('In development'))
                ->description(__('In development'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->extraAttributes(['wire:click' => '$dispatch("open-crm-modal", { modal: "returns" })', 'class' => 'cursor-pointer']),
            Stat::make(__('Potential future revenue'), __('In development'))
                ->description(__('Value added this month') . ': ' . __('In development'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success')
                ->extraAttributes(['wire:click' => '$dispatch("open-crm-modal", { modal: "future-revenue" })', 'class' => 'cursor-pointer']),
            Stat::make(__('Customer status'), __('In development'))
                ->description(__('In development'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->extraAttributes(['wire:click' => '$dispatch("open-crm-modal", { modal: "customer-status" })', 'class' => 'cursor-pointer']),
        ];
    }
}
