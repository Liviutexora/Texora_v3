<?php

namespace App\Http\Controllers;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingCalendarController extends Controller
{
    /**
     * Return FullCalendar-compatible event objects for the authenticated tenant.
     */
    public function events(Request $request): JsonResponse
    {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return response()->json([]);
        }

        $start = $request->query('start', now()->startOfMonth()->toDateString());
        $end   = $request->query('end',   now()->endOfMonth()->toDateString());

        $events = SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [substr($start, 0, 10), substr($end, 0, 10)])
            ->with('service')
            ->get()
            ->map(fn ($b) => [
                'id'              => $b->id,
                'title'           => ($b->service?->name ?? 'Booking') . ' – ' . $b->name,
                'start'           => $b->date->format('Y-m-d') . 'T' . $b->start_time,
                'end'             => $b->date->format('Y-m-d') . 'T' . $b->end_time,
                'backgroundColor' => match ($b->status) {
                    'confirmed' => '#10b981',
                    'completed' => '#6366f1',
                    'cancelled' => '#ef4444',
                    'no_show'   => '#9ca3af',
                    default     => '#f59e0b',
                },
                'borderColor' => match ($b->status) {
                    'confirmed' => '#059669',
                    'completed' => '#4f46e5',
                    'cancelled' => '#dc2626',
                    'no_show'   => '#6b7280',
                    default     => '#d97706',
                },
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'bookingId' => $b->id,
                ],
            ]);

        return response()->json($events);
    }

    /**
     * Return full booking detail for the modal popup on the calendar page.
     */
    public function show(int $id): JsonResponse
    {
        $tenantId = TenantContext::id();

        $booking = SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with('service')
            ->findOrFail($id);

        // Provider name: provider_id references users.id
        $providerName = \App\Models\User::find($booking->provider_id)?->name ?? '—';

        $statusLabel = match ($booking->status) {
            'confirmed' => __('Confirmed'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
            'no_show'   => __('No Show'),
            default     => __('Pending'),
        };

        $statusColor = match ($booking->status) {
            'confirmed' => '#10b981',
            'completed' => '#6366f1',
            'cancelled' => '#ef4444',
            'no_show'   => '#9ca3af',
            default     => '#f59e0b',
        };

        $paymentLabel = match ($booking->payment_status ?? 'unpaid') {
            'paid'    => __('Paid'),
            'partial' => __('Partial'),
            'refunded'=> __('Refunded'),
            default   => __('Unpaid'),
        };

        return response()->json([
            'id'             => $booking->id,
            'date'           => $booking->date->format('l, d M Y'),
            'start_time'     => substr($booking->start_time, 0, 5),
            'end_time'       => substr($booking->end_time,   0, 5),
            'service'        => $booking->service?->name ?? '—',
            'provider'       => $providerName,
            'client'         => $booking->name,
            'email'          => $booking->email,
            'phone'          => $booking->phone,
            'note'           => $booking->note,
            'status'         => $statusLabel,
            'status_color'   => $statusColor,
            'payment_status' => $paymentLabel,
            'amount'         => $booking->amount ? number_format((float) $booking->amount, 2) : null,
            'currency'       => $booking->currency ?? 'INR',
            'cancellation_reason' => $booking->cancellation_reason,
            'view_url'       => route('filament.tenant.resources.bookings.view', $booking->id),
        ]);
    }
}
