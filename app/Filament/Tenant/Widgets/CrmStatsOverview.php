<?php

namespace App\Filament\Tenant\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmStatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make(__('Reminder clienți'), 0)
                ->description(__('0 astăzi · 0 în următoarele 7 zile'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->url(route('filament.tenant.pages.reveniri-clienti'))
                ->color('primary'),
            Stat::make(__('Campaigns'), __('In development'))
                ->description(__('In development'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary')
                ->extraAttributes(['wire:click' => '$dispatch("open-crm-modal", { modal: "campaigns" })', 'class' => 'cursor-pointer']),
            Stat::make(__('Customer reviews'), __('In development'))
                ->description(__('In development'))
                ->descriptionIcon('heroicon-m-star')
                ->color('primary')
                ->extraAttributes(['wire:click' => '$dispatch("open-crm-modal", { modal: "reviews" })', 'class' => 'cursor-pointer']),
        ];
    }
}
