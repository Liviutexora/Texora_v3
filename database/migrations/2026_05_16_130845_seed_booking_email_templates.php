<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $templates = [
            [
                'slug'         => 'booking_confirmation',
                'name'         => 'Booking Confirmation',
                'subject'      => 'Booking Confirmed — {{SERVICE_NAME}} at {{TENANT_NAME}}',
                'body'         => '<p>Hi <strong>{{CLIENT_NAME}}</strong>,</p>
<p>Your booking is <strong>Confirmed</strong>. We look forward to seeing you!</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Booking #</td><td><strong>{{BOOKING_ID}}</strong></td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Time</td><td>{{BOOKING_TIME}}</td></tr>
</table>
<p style="font-size:13px;color:#6b7280;">Need to cancel? <a href="{{CANCEL_URL}}">Click here</a> to cancel this booking.</p>
<p style="font-size:13px;color:#6b7280;">A calendar invite (.ics) is attached to this email.</p>',
                'placeholders' => json_encode([
                    '{{CLIENT_NAME}}', '{{SERVICE_NAME}}', '{{BOOKING_DATE}}', '{{BOOKING_TIME}}',
                    '{{BOOKING_ID}}', '{{PROVIDER_NAME}}', '{{AMOUNT}}', '{{CANCEL_URL}}', '{{TENANT_NAME}}',
                ]),
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'slug'         => 'booking_cancellation',
                'name'         => 'Booking Cancellation',
                'subject'      => 'Booking Cancelled — {{SERVICE_NAME}}',
                'body'         => '<p>Hi <strong>{{CLIENT_NAME}}</strong>,</p>
<p>Your booking has been <strong>cancelled</strong>.</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Booking #</td><td><strong>{{BOOKING_ID}}</strong></td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Time</td><td>{{BOOKING_TIME}}</td></tr>
</table>
<p><a href="{{BOOK_AGAIN_URL}}" style="display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Book Again</a></p>',
                'placeholders' => json_encode([
                    '{{CLIENT_NAME}}', '{{SERVICE_NAME}}', '{{BOOKING_DATE}}', '{{BOOKING_TIME}}',
                    '{{BOOKING_ID}}', '{{CANCELLATION_REASON}}', '{{BOOK_AGAIN_URL}}', '{{TENANT_NAME}}',
                ]),
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'slug'         => 'booking_reminder',
                'name'         => 'Booking Reminder',
                'subject'      => 'Reminder: {{SERVICE_NAME}} tomorrow',
                'body'         => '<p>Hi <strong>{{CLIENT_NAME}}</strong>,</p>
<p>This is a friendly reminder that you have an appointment <strong>tomorrow</strong>.</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Time</td><td>{{BOOKING_TIME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Location</td><td>{{LOCATION}}</td></tr>
</table>
<p style="font-size:13px;color:#6b7280;">Need to cancel? <a href="{{CANCEL_URL}}">Click here</a> to cancel this booking.</p>',
                'placeholders' => json_encode([
                    '{{CLIENT_NAME}}', '{{SERVICE_NAME}}', '{{BOOKING_DATE}}', '{{BOOKING_TIME}}',
                    '{{LOCATION}}', '{{CANCEL_URL}}', '{{TENANT_NAME}}',
                ]),
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'slug'         => 'provider_new_booking',
                'name'         => 'Provider New Booking',
                'subject'      => 'New Booking — {{SERVICE_NAME}} on {{BOOKING_DATE}}',
                'body'         => '<p>Hi,</p>
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
<p><a href="{{DASHBOARD_URL}}" style="display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">View in Dashboard</a></p>',
                'placeholders' => json_encode([
                    '{{CLIENT_NAME}}', '{{SERVICE_NAME}}', '{{BOOKING_DATE}}', '{{BOOKING_TIME}}',
                    '{{BOOKING_ID}}', '{{CLIENT_EMAIL}}', '{{CLIENT_PHONE}}', '{{NOTE}}',
                    '{{DASHBOARD_URL}}', '{{TENANT_NAME}}',
                ]),
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('email_templates')
                ->where('slug', $template['slug'])
                ->exists() || DB::table('email_templates')->insert($template);
        }
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('slug', [
            'booking_confirmation',
            'booking_cancellation',
            'booking_reminder',
            'provider_new_booking',
        ])->delete();
    }
};
