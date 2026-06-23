<?php

namespace App\Jobs;

use App\Models\NotificationPreference;
use App\Models\SlotReservation;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookingRescheduledEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly SlotReservation $booking) {}

    public function handle(): void
    {
        if (! $this->booking->email) {
            return;
        }

        if (! NotificationPreference::isEmailEnabled('booking_rescheduled')) {
            return;
        }

        $booking   = $this->booking->load(['tenant', 'service', 'providerRelation.user']);
        $tenant    = $booking->tenant;
        $cancelUrl = rescue(fn () => route('booking.cancel', $booking->cancellation_token), '');

        $placeholders = [
            'CLIENT_NAME'   => htmlspecialchars($booking->name ?? '', ENT_QUOTES, 'UTF-8'),
            'SERVICE_NAME'  => $booking->service?->name ?? '',
            'BOOKING_DATE'  => $booking->date?->format('D, d M Y') ?? '',
            'BOOKING_TIME'  => substr($booking->start_time ?? '', 0, 5),
            'BOOKING_ID'    => '#' . $booking->id,
            'PROVIDER_NAME' => $booking->providerRelation?->user?->name ?? '',
            'CANCEL_URL'    => $cancelUrl,
            'TENANT_NAME'   => $tenant?->name ?? '',
        ];

        $fallbackBody = <<<HTML
<p>Hi <strong>{{CLIENT_NAME}}</strong>,</p>
<p>Your appointment has been <strong>rescheduled</strong>. Here are your updated details:</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Booking #</td><td><strong>{{BOOKING_ID}}</strong></td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">New Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">New Time</td><td>{{BOOKING_TIME}}</td></tr>
</table>
<p style="font-size:13px;color:#6b7280;">Need to cancel? <a href="{{CANCEL_URL}}">Click here</a> to cancel this booking.</p>
<p style="font-size:13px;color:#6b7280;">An updated calendar invite (.ics) is attached to this email.</p>
HTML;

        ['subject' => $subject, 'html' => $html] = EmailTemplateService::renderWithLayoutFallback(
            subjectFallback: "Your {$placeholders['SERVICE_NAME']} appointment has been rescheduled",
            bodyFallback: $fallbackBody,
            placeholders: $placeholders,
            templateSlug: 'booking_rescheduled',
        );

        $ics = $this->buildIcs($booking);

        Mail::send([], [], function ($message) use ($booking, $subject, $html, $ics) {
            $message->to($booking->email, $booking->name)
                ->subject($subject)
                ->html($html)
                ->attachData($ics, 'booking.ics', ['mime' => 'text/calendar']);
        });
    }

    private function buildIcs(SlotReservation $booking): string
    {
        $tenant   = $booking->tenant;
        $start    = $booking->date->format('Ymd') . 'T' . str_replace(':', '', substr($booking->start_time, 0, 5)) . '00';
        $end      = $booking->date->format('Ymd') . 'T' . str_replace(':', '', substr($booking->end_time ?? $booking->start_time, 0, 5)) . '00';
        $uid      = "booking-{$booking->id}@" . parse_url(config('app.url'), PHP_URL_HOST);
        $now      = now()->format('Ymd\THis\Z');
        $summary  = addcslashes("{$booking->service?->name} at {$tenant?->name}", '\\,;');
        $location = addcslashes($tenant?->address ?? $tenant?->name ?? '', '\\,;');

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Slotara//Booking//EN',
            'METHOD:REQUEST',
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTAMP:{$now}",
            "DTSTART:{$start}",
            "DTEND:{$end}",
            "SUMMARY:{$summary}",
            "LOCATION:{$location}",
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);
    }
}
