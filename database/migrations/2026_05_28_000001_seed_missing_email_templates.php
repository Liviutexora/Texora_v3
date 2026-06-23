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
                'name'         => 'Booking Rescheduled',
                'slug'         => 'booking_rescheduled',
                'subject'      => 'Your {{SERVICE_NAME}} appointment has been rescheduled',
                'body'         => '<p>Hi <strong>{{CLIENT_NAME}}</strong>,</p>
<p>Your appointment has been <strong>rescheduled</strong>. Here are your updated details:</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Booking #</td><td><strong>{{BOOKING_ID}}</strong></td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">New Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">New Time</td><td>{{BOOKING_TIME}}</td></tr>
</table>
<p style="font-size:13px;color:#6b7280;">Need to cancel? <a href="{{CANCEL_URL}}">Click here</a> to cancel this booking.</p>
<p style="font-size:13px;color:#6b7280;">An updated calendar invite (.ics) is attached to this email.</p>',
                'placeholders' => json_encode([
                    '{{CLIENT_NAME}}', '{{SERVICE_NAME}}', '{{BOOKING_DATE}}', '{{BOOKING_TIME}}',
                    '{{BOOKING_ID}}', '{{PROVIDER_NAME}}', '{{CANCEL_URL}}', '{{TENANT_NAME}}',
                ]),
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'name'         => 'Payment Failed',
                'slug'         => 'payment_failed',
                'subject'      => 'Action required: payment failed for {{BUSINESS_NAME}}',
                'body'         => '<p>Hi <strong>{{OWNER_NAME}}</strong>,</p>
<p>We were unable to process the latest payment for your <strong>{{BUSINESS_NAME}}</strong> subscription on {{SITE_NAME}}.</p>
<p>Your account is now marked as <strong>past due</strong>. To avoid any interruption to your booking page, please update your payment method as soon as possible.</p>
<p style="margin:24px 0;">
    <a href="{{BILLING_URL}}" style="display:inline-block;padding:12px 24px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Update Payment Method</a>
</p>
<p style="font-size:13px;color:#6b7280;">If you have any questions, reply to this email — we\'re happy to help.</p>',
                'placeholders' => json_encode([
                    '{{OWNER_NAME}}', '{{BUSINESS_NAME}}', '{{SITE_NAME}}', '{{BILLING_URL}}',
                ]),
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'name'         => 'Subscription Cancelled',
                'slug'         => 'subscription_cancelled',
                'subject'      => 'Your {{SITE_NAME}} subscription has been cancelled',
                'body'         => '<p>Hi <strong>{{OWNER_NAME}}</strong>,</p>
<p>Your subscription for <strong>{{BUSINESS_NAME}}</strong> on {{SITE_NAME}} has been cancelled and your account has been suspended.</p>
<p>Your booking page is no longer accepting new appointments. If you\'d like to reactivate your account, you can resubscribe at any time from your billing page.</p>
<p style="margin:24px 0;">
    <a href="{{BILLING_URL}}" style="display:inline-block;padding:12px 24px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Resubscribe Now</a>
</p>
<p style="font-size:13px;color:#6b7280;">If you believe this was a mistake or have any questions, reply to this email.</p>',
                'placeholders' => json_encode([
                    '{{OWNER_NAME}}', '{{BUSINESS_NAME}}', '{{SITE_NAME}}', '{{BILLING_URL}}',
                ]),
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('email_templates')
                ->insertOrIgnore($template);
        }
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->whereIn('slug', ['booking_rescheduled', 'payment_failed', 'subscription_cancelled'])
            ->delete();
    }
};
