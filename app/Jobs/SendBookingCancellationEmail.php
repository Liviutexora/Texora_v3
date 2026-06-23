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

class SendBookingCancellationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly SlotReservation $booking) {}

    public function handle(): void
    {
        if (! $this->booking->email) {
            return;
        }

        if (! NotificationPreference::isEmailEnabled('booking_cancellation')) {
            return;
        }

        $booking  = $this->booking->load(['tenant', 'service']);
        $tenant   = $booking->tenant;

        $e = fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        $placeholders = [
            'CLIENT_NAME'         => $e($booking->name ?? ''),
            'SERVICE_NAME'        => $booking->service?->name ?? '',
            'BOOKING_DATE'        => $booking->date?->format('D, d M Y') ?? '',
            'BOOKING_TIME'        => substr($booking->start_time ?? '', 0, 5),
            'BOOKING_ID'          => '#' . $booking->id,
            'CANCELLATION_REASON' => $e($booking->cancellation_reason ?? ''),
            'BOOK_AGAIN_URL'      => url('/' . ($tenant?->slug ?? '')),
            'TENANT_NAME'         => $tenant?->name ?? '',
        ];

        $cancelledMsg = __('Your booking has been cancelled.');
        $bookAgain    = __('Book Again');

        $fallbackBody = <<<HTML
<p>Hi <strong>{{CLIENT_NAME}}</strong>,</p>
<p>{$cancelledMsg}</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Booking #</td><td><strong>{{BOOKING_ID}}</strong></td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Time</td><td>{{BOOKING_TIME}}</td></tr>
</table>
<p><a href="{{BOOK_AGAIN_URL}}" style="display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">{$bookAgain}</a></p>
HTML;

        EmailTemplateService::sendWithLayoutFallback(
            to: $booking->email,
            subjectFallback: __('Booking Cancelled') . " — {$placeholders['SERVICE_NAME']}",
            bodyFallback: $fallbackBody,
            placeholders: $placeholders,
            templateSlug: 'booking_cancellation',
        );
    }
}
