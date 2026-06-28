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
            Stat::make(__('Reveniri clienți'), 0)
                ->description(__('0 astăzi • 0 în următoarele 7 zile'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->url(route('filament.tenant.pages.reveniri-clienti'))
                ->color('primary'),
        ];
    }
}
