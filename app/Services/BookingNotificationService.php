<?php

namespace App\Services;

use App\Helpers\NotificationHelper;
use App\Jobs\SendBookingConfirmationEmail;
use App\Jobs\SendBookingConfirmationSms;
use App\Jobs\SendProviderNewBookingEmail;
use App\Jobs\SendWebhookPayload;
use App\Models\SlotReservation;

class BookingNotificationService
{
    public function dispatchForNewBooking(
        SlotReservation $booking,
        bool $emailConfirmation = true,
        bool $notifyOwner = true,
    ): void {
        if (! $booking->exists) {
            return;
        }

        if ($emailConfirmation) {
            SendBookingConfirmationEmail::dispatch($booking)->afterCommit();
        }

        SendBookingConfirmationSms::dispatch($booking)->afterCommit();

        if ($notifyOwner) {
            SendProviderNewBookingEmail::dispatch($booking)->afterCommit();
        }

        $this->dispatchWebAndStaffNotifications($booking);

        $webhookUrl = (string) \App\Models\Setting::get("tenant_{$booking->tenant_id}_webhook_url", '');
        if ($webhookUrl !== '') {
            SendWebhookPayload::dispatch($booking)->afterCommit();
        }
    }

    public function dispatchWebAndStaffNotifications(SlotReservation $booking): void
    {
        try {
            $url = rescue(fn () => route('filament.tenant.resources.bookings.view', ['record' => $booking->id]), null);
            NotificationHelper::sendToTenantWebUsers(
                'new_booking',
                $booking->tenant_id,
                __('New Booking'),
                __('New booking from :name for :service', [
                    'name' => $booking->name,
                    'service' => $booking->service?->name,
                ]),
                $url
            );
        } catch (\Throwable) {
            // Non-critical
        }

        try {
            $booking->loadMissing(['tenant', 'service']);
            $e = fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
            $dashboardUrl = rescue(fn () => route('filament.tenant.pages.tenant-dashboard'), '');
            NotificationHelper::sendEmailToTenantUsers(
                event: 'new_booking',
                tenantId: $booking->tenant_id,
                subjectFallback: __('New Booking') . " — {$booking->service?->name} on {$booking->date?->format('D, d M Y')}",
                bodyFallback: <<<HTML
<p>Hi,</p>
<p>You have a <strong>new booking</strong> from a client.</p>
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
<p><a href="{{DASHBOARD_URL}}" style="display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">View in Dashboard</a></p>
HTML,
                placeholders: [
                    'CLIENT_NAME'   => $e($booking->name ?? ''),
                    'SERVICE_NAME'  => $booking->service?->name ?? '',
                    'BOOKING_DATE'  => $booking->date?->format('D, d M Y') ?? '',
                    'BOOKING_TIME'  => substr($booking->start_time ?? '', 0, 5),
                    'BOOKING_ID'    => '#' . $booking->id,
                    'CLIENT_EMAIL'  => $e($booking->email ?? ''),
                    'CLIENT_PHONE'  => $e($booking->phone ?? ''),
                    'NOTE'          => $booking->note ? '<p><strong>Note:</strong> ' . nl2br($e($booking->note)) . '</p>' : '',
                    'DASHBOARD_URL' => $dashboardUrl,
                    'TENANT_NAME'   => $booking->tenant?->name ?? '',
                ],
                templateSlug: 'provider_new_booking',
            );
        } catch (\Throwable) {
            // Non-critical
        }
    }
}
