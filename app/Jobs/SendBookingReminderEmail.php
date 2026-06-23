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

class SendBookingReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly SlotReservation $booking) {}

    public function handle(): void
    {
        if (! $this->booking->email) {
            return;
        }

        // Idempotency guard — skip if already sent
        if ($this->booking->reminder_sent_at) {
            return;
        }

        if (! NotificationPreference::isEmailEnabled('booking_reminder')) {
            return;
        }

        $booking    = $this->booking->load(['tenant', 'service']);
        $tenant     = $booking->tenant;
        $cancelUrl  = rescue(fn () => route('booking.cancel', $booking->cancellation_token), '');

        $placeholders = [
            'CLIENT_NAME'  => htmlspecialchars($booking->name ?? '', ENT_QUOTES, 'UTF-8'),
            'SERVICE_NAME' => $booking->service?->name ?? '',
            'BOOKING_DATE' => $booking->date?->format('D, d M Y') ?? '',
            'BOOKING_TIME' => substr($booking->start_time ?? '', 0, 5),
            'LOCATION'     => $tenant?->address ?? $tenant?->name ?? '',
            'CANCEL_URL'   => $cancelUrl,
            'TENANT_NAME'  => $tenant?->name ?? '',
        ];

        $fallbackBody = <<<HTML
<p>Hi <strong>{{CLIENT_NAME}}</strong>,</p>
<p>This is a friendly reminder that you have an appointment <strong>tomorrow</strong>.</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Time</td><td>{{BOOKING_TIME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Location</td><td>{{LOCATION}}</td></tr>
</table>
<p style="font-size:13px;color:#6b7280;">Need to cancel? <a href="{{CANCEL_URL}}">Click here</a> to cancel this booking.</p>
HTML;

        EmailTemplateService::sendWithLayoutFallback(
            to: $booking->email,
            subjectFallback: "Reminder: {$placeholders['SERVICE_NAME']} tomorrow",
            bodyFallback: $fallbackBody,
            placeholders: $placeholders,
            templateSlug: 'booking_reminder',
        );

        $this->booking->updateQuietly(['reminder_sent_at' => now()]);
    }
}
