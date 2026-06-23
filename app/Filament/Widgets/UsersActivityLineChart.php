<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasWidgetPermissions;
use App\Models\UserVisit;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class UsersActivityLineChart extends ChartWidget
{
    use HasWidgetPermissions;

    public function getHeading(): ?string
    {
        return __('Site visits (last 12 months)');
    }

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect();
        $totalVisits = collect();
        $loggedInVisits = collect();

        // Last 12 months including current
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthLabel = $month->format('M Y'); // e.g., Sep 2025
            $months->push($monthLabel);

            // Total visits in this month
            $total = UserVisit::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $totalVisits->push($total);

            // Logged-in visits in this month
            $loggedIn = UserVisit::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->whereNotNull('user_id')
                ->count();

            $loggedInVisits->push($loggedIn);
        }

        return [
            'labels' => $months->toArray(),
            'datasets' => [
                [
                    'label' => 'Total Visits',
                    'data' => $totalVisits->toArray(),
                    'borderColor' => 'rgba(255,99,132,1)',
                    'backgroundColor' => 'rgba(255,99,132,0.2)',
                    'fill' => true,
                    'tension' => 0.3,
                    'pointBackgroundColor' => 'rgba(255,99,132,1)',
                ],
                [
                    'label' => 'Logged-in Visits',
                    'data' => $loggedInVisits->toArray(),
                    'borderColor' => 'rgba(54,162,235,1)',
                    'backgroundColor' => 'rgba(54,162,235,0.2)',
                    'fill' => true,
                    'tension' => 0.3,
                    'pointBackgroundColor' => 'rgba(54,162,235,1)',
                ],
            ],
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'sm' => 1,
            'md' => 1,
            'xl' => 1,
        ];
    }
}
