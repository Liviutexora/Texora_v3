<?php

namespace App\Jobs;

use App\Models\NotificationPreference;
use App\Models\Provider;
use App\Models\SlotReservation;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendProviderNewBookingEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly SlotReservation $booking) {}

    public function handle(): void
    {
        if (! NotificationPreference::isEmailEnabled('provider_new_booking')) {
            return;
        }

        $provider = Provider::withoutGlobalScope('tenant')
            ->with('user')
            ->find($this->booking->provider_id);

        $email = $provider?->user?->email;

        if (! $email) {
            return;
        }

        $booking  = $this->booking->load(['tenant', 'service']);
        $tenant   = $booking->tenant;
        $dashboardUrl = rescue(fn () => route('filament.manage.pages.dashboard'), '');

        $e = fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        $placeholders = [
            'CLIENT_NAME'   => $e($booking->name ?? ''),
            'SERVICE_NAME'  => $booking->service?->name ?? '',
            'BOOKING_DATE'  => $booking->date?->format('D, d M Y') ?? '',
            'BOOKING_TIME'  => substr($booking->start_time ?? '', 0, 5),
            'BOOKING_ID'    => '#' . $booking->id,
            'CLIENT_EMAIL'  => $e($booking->email ?? ''),
            'CLIENT_PHONE'  => $e($booking->phone ?? ''),
            'NOTE'          => nl2br($e($booking->note ?? '')),
            'DASHBOARD_URL' => $dashboardUrl,
            'TENANT_NAME'   => $tenant?->name ?? '',
        ];

        $newBookingMsg = __('You have a new booking from a client.');
        $viewDashboard = __('View in Dashboard');

        $fallbackBody = <<<HTML
<p>Hi,</p>
<p>{$newBookingMsg}</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Booking #</td><td><strong>{{BOOKING_ID}}</strong></td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Client</td><td>{{CLIENT_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Time</td><td>{{BOOKING_TIME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Email</td><td>{{CLIENT_EMAIL}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Phone</td><td>{{CLIENT_PHONE}}</td></tr>
</table>
{{NOTE}}
<p><a href="{{DASHBOARD_URL}}" style="display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">{$viewDashboard}</a></p>
HTML;

        EmailTemplateService::sendWithLayoutFallback(
            to: $email,
            subjectFallback: __('New Booking') . " — {$placeholders['SERVICE_NAME']} on {$placeholders['BOOKING_DATE']}",
            bodyFallback: $fallbackBody,
            placeholders: $placeholders,
            templateSlug: 'provider_new_booking',
        );
    }
}
