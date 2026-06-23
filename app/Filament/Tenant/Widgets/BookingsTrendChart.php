<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class BookingsTrendChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public string $period = 'this_month';

    public function getHeading(): string
    {
        return __('Bookings Trend') . ' (' . $this->periodLabel() . ')';
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

    protected function getData(): array
    {
        $tenantId = TenantContext::id();

        [$start, $end] = $this->periodDates();
        $diffDays      = $start->diffInDays($end) + 1;
        $groupByWeek   = $diffDays > 60;

        if ($groupByWeek) {
            $raw = SlotReservation::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw("DATE_FORMAT(date, '%Y-%u') as period, COUNT(*) as total")
                ->groupBy('period')
                ->pluck('total', 'period');

            $current = $start->copy()->startOfWeek();
            $labels  = [];
            $data    = [];
            while ($current->lte($end)) {
                $key      = $current->format('Y-W');
                $labels[] = $current->format('d M');
                $data[]   = (int) ($raw[$key] ?? 0);
                $current->addWeek();
            }
        } else {
            $raw = SlotReservation::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('DATE(date) as period, COUNT(*) as total')
                ->groupBy('period')
                ->pluck('total', 'period');

            $days    = (int) $diffDays;
            $periods = collect(range($days - 1, 0))->map(fn ($d) => $end->copy()->subDays($d));
            $labels  = $periods->map(fn ($d) => $d->format('d M'))->toArray();
            $data    = $periods->map(fn ($d) => (int) ($raw[$d->toDateString()] ?? 0))->toArray();
        }

        return [
            'datasets' => [[
                'label'           => __('Bookings'),
                'data'            => $data,
                'borderColor'     => '#7c3aed',
                'backgroundColor' => 'rgba(124, 58, 237, 0.08)',
                'fill'            => true,
                'tension'         => 0.4,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
