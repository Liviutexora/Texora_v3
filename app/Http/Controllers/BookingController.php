<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Jobs\SendBookingCancellationEmail;
use App\Jobs\SendBookingCancellationSms;
use App\Models\SlotReservation;
use App\Services\BookingPaymentService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function ical(string $token)
    {
        $booking = SlotReservation::withoutGlobalScope('tenant')
            ->with(['tenant', 'service'])
            ->where('cancellation_token', $token)
            ->firstOrFail();

        $start   = $booking->date->format('Ymd') . 'T' . str_replace(':', '', substr($booking->start_time, 0, 5)) . '00';
        $end     = $booking->date->format('Ymd') . 'T' . str_replace(':', '', substr($booking->end_time ?? $booking->start_time, 0, 5)) . '00';
        $uid     = "booking-{$booking->id}@" . parse_url(config('app.url'), PHP_URL_HOST);
        $now     = now()->format('Ymd\THis\Z');
        $summary = addcslashes("{$booking->service?->name} at {$booking->tenant?->name}", '\\,;');
        $location = addcslashes($booking->tenant?->address ?? $booking->tenant?->name ?? '', '\\,;');

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Slotara//Booking//EN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            "UID:$uid",
            "DTSTAMP:$now",
            "DTSTART:$start",
            "DTEND:$end",
            "SUMMARY:$summary",
            "LOCATION:$location",
            "DESCRIPTION:Booking #{$booking->id}",
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        return response($ics, 200, [
            'Content-Type'        => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"booking-{$booking->id}.ics\"",
        ]);
    }

    public function cancelShow(string $token)
    {
        $booking = SlotReservation::withoutGlobalScope('tenant')
            ->with(['tenant', 'service'])
            ->where('cancellation_token', $token)
            ->firstOrFail();

        if (! in_array($booking->status, ['pending', 'confirmed'])) {
            return view('booking.cancel-invalid', compact('booking'));
        }

        return view('booking.cancel', compact('booking'));
    }

    public function cancelConfirm(Request $request, string $token)
    {
        $booking = SlotReservation::withoutGlobalScope('tenant')
            ->with(['tenant', 'service'])
            ->where('cancellation_token', $token)
            ->firstOrFail();

        if (! in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()->back()->with('error', __('This booking cannot be cancelled.'));
        }

        $booking->update([
            'status'               => 'cancelled',
            'cancelled_at'         => now(),
            'cancellation_reason'  => __('Cancelled by client'),
            'cancellation_token'   => null, // invalidate token after use
        ]);

        SendBookingCancellationEmail::dispatch($booking)->afterCommit();
        SendBookingCancellationSms::dispatch($booking)->afterCommit();
        app(BookingPaymentService::class)->maybeDeleteCalendar($booking->fresh());

        try {
            $url = rescue(fn () => route('filament.tenant.resources.bookings.view', ['record' => $booking->id]), null);
            NotificationHelper::sendToTenantWebUsers(
                'booking_cancelled',
                $booking->tenant_id,
                __('Booking Cancelled'),
                __('Booking #:id cancelled by :name', ['id' => $booking->id, 'name' => $booking->name]),
                $url,
            );
        } catch (\Throwable) {
            // Non-critical
        }

        // Email opted-in tenant users (owner/staff) about the cancellation
        try {
            $e = fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
            $dashboardUrl = rescue(fn () => route('filament.manage.pages.dashboard'), '');
            $cancelledMsg  = __('A booking has been cancelled by the client.');
            $viewDashboard = __('View in Dashboard');
            NotificationHelper::sendEmailToTenantUsers(
                event: 'booking_cancelled',
                tenantId: $booking->tenant_id,
                subjectFallback: __('Booking Cancelled') . " — {$booking->service?->name} by {$booking->name}",
                bodyFallback: <<<HTML
<p>Hi,</p>
<p>{$cancelledMsg}</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Booking #</td><td><strong>{{BOOKING_ID}}</strong></td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Client</td><td>{{CLIENT_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Time</td><td>{{BOOKING_TIME}}</td></tr>
</table>
<p><a href="{{DASHBOARD_URL}}" style="display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">{$viewDashboard}</a></p>
HTML,
                placeholders: [
                    'BOOKING_ID'    => '#' . $booking->id,
                    'CLIENT_NAME'   => $e($booking->name ?? ''),
                    'SERVICE_NAME'  => $booking->service?->name ?? '',
                    'BOOKING_DATE'  => $booking->date?->format('D, d M Y') ?? '',
                    'BOOKING_TIME'  => substr($booking->start_time ?? '', 0, 5),
                    'DASHBOARD_URL' => $dashboardUrl,
                ],
            );
        } catch (\Throwable) {
            // Non-critical
        }

        return view('booking.cancel-success', compact('booking'));
    }
}
