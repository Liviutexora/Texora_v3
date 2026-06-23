<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class BookingCalendarWidget extends FullCalendarWidget
{
    protected int | string | array $columnSpan = 'full';

    /**
     * FullCalendar calls this whenever the visible date range changes (month/week/day nav).
     *
     * @param array{start: string, end: string, timezone: string} $info
     */
    public function fetchEvents(array $info): array
    {
        $tenantId = TenantContext::id();

        return SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$info['start'], $info['end']])
            ->with('service')
            ->get()
            ->map(fn ($b) => [
                'id'              => $b->id,
                'title'           => trim(($b->service?->name ?? '') . ' — ' . $b->name),
                'start'           => $b->date->format('Y-m-d') . 'T' . $b->start_time,
                'end'             => $b->date->format('Y-m-d') . 'T' . $b->end_time,
                'url'             => route('filament.tenant.resources.bookings.view', $b->id),
                'backgroundColor' => match ($b->status) {
                    'confirmed' => '#10b981',
                    'completed' => '#6366f1',
                    'cancelled' => '#ef4444',
                    'no_show'   => '#9ca3af',
                    default     => '#f59e0b',   // pending
                },
                'borderColor'     => match ($b->status) {
                    'confirmed' => '#059669',
                    'completed' => '#4f46e5',
                    'cancelled' => '#dc2626',
                    'no_show'   => '#6b7280',
                    default     => '#d97706',
                },
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'status'  => $b->status,
                    'phone'   => $b->phone,
                    'service' => $b->service?->name,
                ],
            ])
            ->toArray();
    }

    public function config(): array
    {
        return [
            'initialView'         => 'dayGridMonth',
            'headerToolbar'       => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            ],
            'height'              => 'auto',
            'firstDay'            => 1,         // Monday start
            'nowIndicator'        => true,
            'dayMaxEvents'        => 3,         // "+N more" popover
            'eventTimeFormat'     => [
                'hour'   => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
            'slotMinTime'         => '07:00:00',
            'slotMaxTime'         => '22:00:00',
        ];
    }
}
