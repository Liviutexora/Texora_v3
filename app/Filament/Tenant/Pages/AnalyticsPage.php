<?php

namespace App\Filament\Tenant\Pages;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class AnalyticsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = null;
    protected static ?string $title           = null;
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    public static function getNavigationGroup(): ?string { return __('Operations'); }
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.tenant.pages.analytics-page';

    public string $period = 'this_month';

    public static function getNavigationLabel(): string
    {
        return __('Analytics');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Analytics');
    }

    public function updatedPeriod(): void
    {
        $this->dispatch('analytics.period.changed', period: $this->period);
    }

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    /** Resolve (start, end) Carbon dates for the selected period. */
    private function periodDates(): array
    {
        return match ($this->period) {
            'last_month'    => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'last_3_months' => [now()->subMonths(3)->startOfMonth(), now()->endOfMonth()],
            'last_6_months' => [now()->subMonths(6)->startOfMonth(), now()->endOfMonth()],
            'this_year'     => [now()->startOfYear(), now()->endOfYear()],
            default         => [now()->startOfMonth(), now()->endOfMonth()],   // this_month
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

    public function getViewData(): array
    {
        $tenantId       = TenantContext::id();
        $currency       = TenantContext::current()?->currency ?? 'INR';
        $currencySymbol = self::currencySymbol($currency);

        if (! $tenantId) {
            return $this->emptyData($currency);
        }

        [$start, $end] = $this->periodDates();

        $base = fn () => SlotReservation::withoutGlobalScope('tenant')
            ->where('slot_reservations.tenant_id', $tenantId)
            ->whereBetween('slot_reservations.date', [$start->toDateString(), $end->toDateString()]);

        // ── Core stats ──────────────────────────────────────────────────────
        $statsRow = (clone $base)()
            ->selectRaw("
                COUNT(*) as total,
                SUM(status IN ('confirmed','completed')) as confirmed,
                SUM(status = 'cancelled') as cancelled,
                SUM(status = 'no_show') as no_show,
                SUM(status = 'pending') as pending,
                SUM(CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END) as revenue,
                COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as paid_count
            ")
            ->first();

        $total       = (int)   ($statsRow->total     ?? 0);
        $confirmed   = (int)   ($statsRow->confirmed ?? 0);
        $cancelled   = (int)   ($statsRow->cancelled ?? 0);
        $noShow      = (int)   ($statsRow->no_show   ?? 0);
        $pending     = (int)   ($statsRow->pending   ?? 0);
        $revenue     = (float) ($statsRow->revenue   ?? 0);
        $paidCount   = (int)   ($statsRow->paid_count ?? 0);
        $avgValue    = $paidCount > 0 ? round($revenue / $paidCount, 2) : 0;

        $confirmRate = $total > 0 ? round($confirmed / $total * 100) : 0;
        $cancelRate  = $total > 0 ? round($cancelled / $total * 100) : 0;
        $noShowRate  = $total > 0 ? round($noShow    / $total * 100) : 0;

        // ── New vs returning clients ─────────────────────────────────────────
        // "New" = email appears for the first time in any booking within the period
        $allEmails = (clone $base)()->whereNotNull('slot_reservations.email')->distinct('slot_reservations.email')->pluck('slot_reservations.email');
        $newCount  = 0;
        if ($allEmails->isNotEmpty()) {
            $newCount = SlotReservation::withoutGlobalScope('tenant')
                ->where('slot_reservations.tenant_id', $tenantId)
                ->whereIn('slot_reservations.email', $allEmails)
                ->whereNotNull('slot_reservations.email')
                ->where('slot_reservations.date', '<', $start->toDateString())
                ->distinct('slot_reservations.email')
                ->count('slot_reservations.email');
            // new = appeared in period but NOT before the period
            $totalInPeriod = $allEmails->count();
            $newCount = $totalInPeriod - $newCount;  // clients who had NO prior bookings
        }
        $returningCount = $allEmails->count() - $newCount;

        // ── Trend: bookings per day/week ─────────────────────────────────────
        $trendDays = $start->diffInDays($end) + 1;
        $groupByWeek = $trendDays > 60;

        if ($groupByWeek) {
            $trendRows = (clone $base)()
                ->selectRaw("DATE_FORMAT(slot_reservations.date, '%Y-%u') as period, COUNT(*) as total")
                ->groupBy('period')
                ->orderBy('period')
                ->get();
            $trendLabels = $trendRows->map(fn ($r) => Carbon::parse(substr($r->period, 0, 4) . 'W' . substr($r->period, 5))->format('d M'))->toArray();
        } else {
            $trendRows = (clone $base)()
                ->selectRaw('DATE(slot_reservations.date) as period, COUNT(*) as total')
                ->groupBy('period')
                ->orderBy('period')
                ->get();
            $trendLabels = $trendRows->map(fn ($r) => Carbon::parse($r->period)->format('d M'))->toArray();
        }
        $trendData = $trendRows->pluck('total')->toArray();

        // ── Revenue by service (doughnut) ────────────────────────────────────
        $revenueByService = (clone $base)()
            ->where('payment_status', 'paid')
            ->join('services', 'services.id', '=', 'slot_reservations.service_id')
            ->selectRaw('services.name as label, SUM(slot_reservations.amount) as value')
            ->groupBy('services.name')
            ->orderByDesc('value')
            ->limit(8)
            ->get();

        // ── Bookings by day of week ──────────────────────────────────────────
        $dowRows = (clone $base)()
            ->selectRaw('DAYOFWEEK(slot_reservations.date) as dow, COUNT(*) as total')
            ->groupBy('dow')
            ->pluck('total', 'dow');
        $dowData = collect(range(2, 8))->map(fn ($d) => (int) ($dowRows[$d % 8 === 0 ? 1 : $d] ?? 0))->toArray();

        // ── Peak hours ───────────────────────────────────────────────────────
        $hourRows = (clone $base)()
            ->selectRaw('HOUR(slot_reservations.start_time) as hr, COUNT(*) as total')
            ->groupBy('hr')
            ->pluck('total', 'hr');
        $peakHours = collect(range(7, 21))->map(fn ($h) => (int) ($hourRows[$h] ?? 0))->toArray();
        $peakHourLabels = collect(range(7, 21))->map(fn ($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00')->toArray();

        // ── Top providers ────────────────────────────────────────────────────
        $topProviders = (clone $base)()
            ->join('users', 'users.id', '=', 'slot_reservations.provider_id')
            ->whereNotIn('slot_reservations.status', ['cancelled'])
            ->selectRaw('users.name, COUNT(slot_reservations.id) as bookings, SUM(CASE WHEN slot_reservations.payment_status="paid" THEN slot_reservations.amount ELSE 0 END) as revenue')
            ->groupBy('users.name')
            ->orderByDesc('bookings')
            ->limit(5)
            ->get();

        // ── Top services ─────────────────────────────────────────────────────
        $topServices = (clone $base)()
            ->join('services', 'services.id', '=', 'slot_reservations.service_id')
            ->whereNotIn('slot_reservations.status', ['cancelled'])
            ->selectRaw('services.name, COUNT(slot_reservations.id) as bookings, SUM(CASE WHEN slot_reservations.payment_status="paid" THEN slot_reservations.amount ELSE 0 END) as revenue')
            ->groupBy('services.name')
            ->orderByDesc('bookings')
            ->limit(5)
            ->get();

        $maxProviderBookings = $topProviders->max('bookings') ?: 1;
        $maxServiceBookings  = $topServices->max('bookings')  ?: 1;

        return compact(
            'total', 'confirmed', 'cancelled', 'noShow', 'pending',
            'revenue', 'avgValue', 'paidCount',
            'confirmRate', 'cancelRate', 'noShowRate',
            'newCount', 'returningCount',
            'trendLabels', 'trendData',
            'revenueByService',
            'dowData',
            'peakHours', 'peakHourLabels',
            'topProviders', 'topServices',
            'maxProviderBookings', 'maxServiceBookings',
            'currency', 'currencySymbol',
        ) + ['periodLabel' => $this->periodLabel()];
    }

    private function emptyData(string $currency): array
    {
        return [
            'total' => 0, 'confirmed' => 0, 'cancelled' => 0, 'noShow' => 0, 'pending' => 0,
            'revenue' => 0, 'avgValue' => 0, 'paidCount' => 0,
            'confirmRate' => 0, 'cancelRate' => 0, 'noShowRate' => 0,
            'newCount' => 0, 'returningCount' => 0,
            'trendLabels' => [], 'trendData' => [],
            'revenueByService' => collect(),
            'dowData' => array_fill(0, 7, 0),
            'peakHours' => array_fill(0, 15, 0),
            'peakHourLabels' => [],
            'topProviders' => collect(), 'topServices' => collect(),
            'maxProviderBookings' => 1, 'maxServiceBookings' => 1,
            'currency' => $currency,
            'currencySymbol' => self::currencySymbol($currency),
            'periodLabel' => $this->periodLabel(),
        ];
    }

    private static function currencySymbol(string $code): string
    {
        return match (strtoupper($code)) {
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            'INR' => '₹',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'JPY' => '¥',
            'CNY' => '¥',
            'CHF' => 'Fr',
            'SGD' => 'S$',
            'AED' => 'د.إ',
            'SAR' => '﷼',
            'MYR' => 'RM',
            'HKD' => 'HK$',
            'NZD' => 'NZ$',
            default => $code,
        };
    }
}
