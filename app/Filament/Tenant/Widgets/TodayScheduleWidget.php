<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Filament\Widgets\Widget;

class TodayScheduleWidget extends Widget
{
    protected static ?int $sort = 2;

    protected string $view = 'filament.tenant.widgets.today-schedule';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $tenantId = TenantContext::id();

        // Today's bookings grouped by provider, ordered by start_time
        $bookings = SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['service'])
            ->orderBy('start_time')
            ->get();

        // Group by provider name (provider() → User via provider_id = users.id)
        $byProvider = $bookings->groupBy(fn ($b) => $b->name ?? 'Unknown Provider');

        // Utilization: bookings today vs total providers
        $totalProviders = \App\Models\Provider::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        $activeToday = $bookings->pluck('provider_id')->unique()->count();

        return [
            'bookings'        => $bookings,
            'totalBookings'   => $bookings->count(),
            'totalProviders'  => $totalProviders,
            'activeToday'     => $activeToday,
            'utilizationPct'  => $totalProviders > 0
                ? round($activeToday / $totalProviders * 100)
                : 0,
        ];
    }
}
