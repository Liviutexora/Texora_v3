<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class RevenueByServiceChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    public string $period = 'this_month';

    public function getHeading(): string
    {
        return __('Revenue by Service') . ' (' . $this->periodLabel() . ')';
    }

    #[On('analytics.period.changed')]
    public function onPeriodChanged(string $period): void
    {
        $this->period = $period;
        $this->updateChartData();
    }

    private function periodDates(): array
    {
        return match ($this->period) {
            'last_month'    => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'last_3_months' => [now()->subMonths(3)->startOfMonth(), now()->endOfMonth()],
            'last_6_months' => [now()->subMonths(6)->startOfMonth(), now()->endOfMonth()],
            'this_year'     => [now()->startOfYear(), now()->endOfYear()],
            default         => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function periodLabel(): string
    {
        return match ($this->period) {
            'last_month'    => now()->subMonthNoOverflow()->format('F Y'),
            'last_3_months' => __('Last 3 Months'),
            'last_6_months' => __('Last 6 Months'),
            'this_year'     => __('Year') . ' ' . now()->year,
            default         => now()->format('F Y'),
        };
    }

    protected function getData(): array
    {
        $tenantId = TenantContext::id();
        [$start, $end] = $this->periodDates();

        $rows = SlotReservation::withoutGlobalScope('tenant')
            ->where('slot_reservations.tenant_id', $tenantId)
            ->where('slot_reservations.payment_status', 'paid')
            ->whereBetween('slot_reservations.date', [$start->toDateString(), $end->toDateString()])
            ->join('services', 'services.id', '=', 'slot_reservations.service_id')
            ->selectRaw('services.name as service_name, SUM(slot_reservations.amount) as total')
            ->groupBy('services.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $palette = ['#7c3aed','#2563eb','#059669','#d97706','#dc2626','#0891b2','#db2777','#65a30d'];

        return [
            'datasets' => [[
                'data'            => $rows->pluck('total')->toArray(),
                'backgroundColor' => array_slice($palette, 0, $rows->count()),
            ]],
            'labels' => $rows->pluck('service_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
