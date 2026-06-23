<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class BookingsByDayOfWeekChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    public string $period = 'this_month';

    public function getHeading(): string
    {
        return __('Busiest Days') . ' (' . $this->periodLabel() . ')';
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
        $days     = [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')];

        [$start, $end] = $this->periodDates();

        $rows = SlotReservation::withoutGlobalScope('tenant')
            ->where('slot_reservations.tenant_id', $tenantId)
            ->whereBetween('slot_reservations.date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('DAYOFWEEK(slot_reservations.date) as dow, COUNT(*) as total')
            ->groupBy('dow')
            ->pluck('total', 'dow');

        // DAYOFWEEK: 1=Sun … 7=Sat → remap to Mon-indexed
        $data = collect(range(2, 8))->map(fn ($d) => (int) ($rows[$d % 8 === 0 ? 1 : $d] ?? 0))->toArray();

        return [
            'datasets' => [[
                'label'           => __('Bookings'),
                'data'            => $data,
                'backgroundColor' => 'rgba(124, 58, 237, 0.7)',
                'borderRadius'    => 6,
            ]],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
