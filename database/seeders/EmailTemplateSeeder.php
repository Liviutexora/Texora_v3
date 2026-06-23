<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $templates = [
            [
                'name' => 'Reset Password Confirmation',
                'subject' => 'Your password has been reset successfully',
                'slug' => 'reset_password_confirmation',
                'body' => '
                    <p>Hello {{NAME}},</p>
                    <p>Your password for {{SITE_NAME}} has been successfully reset.</p>
                    <p>If you did not perform this action, please contact support immediately.</p>
                ',
                'placeholders' => json_encode([
                    '{{NAME}}', '{{EMAIL}}', '{{SITE_NAME}}'
                ]),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'New Registration',
                'subject' => 'New Registration On {{SITE_NAME}}',
                'slug' => 'admin_new_registration',
                'body' => '
<h1>New User Registered</h1>
<p>A new user has successfully registered on <strong>{{SITE_NAME}}</strong>.</p>

<p><strong>Full Name:</strong> {{NAME}}</p>
<p><strong>Email Address:</strong> {{EMAIL}}</p>
                ',
                'placeholders' => json_encode([
                    '{{NAME}}', '{{EMAIL}}', '{{SITE_NAME}}', '{{LOGIN_URL}}'
                ]),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{SITE_NAME}}, {{NAME}}!',
                'slug' => 'user_welcome',
                'body' => '
                    <h1>Welcome, {{NAME}}!</h1>
                    <p>Thanks for signing up at {{SITE_NAME}}. We’re excited to have you on board.</p>
                    <p>Your registered email is: {{EMAIL}}</p>
                    <p>Click below to sign in:</p>
                    <a href="{{LOGIN_URL}}">Sign In</a>
                ',
                'placeholders' => json_encode([
                    '{{NAME}}', '{{EMAIL}}', '{{SITE_NAME}}', '{{LOGIN_URL}}'
                ]),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Forgot Password',
                'subject' => 'Password Reset Request',
                'slug' => 'forgot_password',
                'body' => '
                    <p>Hello {{NAME}},</p>
                    <p>We received a request to reset your password at {{SITE_NAME}}.</p>
                    <p>If this was you, please click the link below:</p>
                    <a href="{{RESET_LINK}}">Reset Password</a>
                    <p>If you did not request this, you can safely ignore this email.</p>
                ',
                'placeholders' => json_encode([
                    '{{NAME}}', '{{EMAIL}}', '{{SITE_NAME}}', '{{RESET_LINK}}'
                ]),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Contact Confirmation',
                'subject' => 'Thank you for contacting {{SITE_NAME}}',
                'slug' => 'user_contact_confirmation',
                'body' => '
                    <h1>Thank You for Contacting Us, {{NAME}}!</h1>
                    <p>We have received your message and will get back to you soon.</p>
                    <p><strong>Your Details:</strong></p>
                    <p><strong>Name:</strong> {{NAME}}</p>
                    <p><strong>Email:</strong> {{EMAIL}}</p>
                    <p><strong>Phone:</strong> {{PHONE}}</p>
                    <p><strong>Your Message:</strong></p>
                    <p>{{MESSAGE}}</p>
                    <p>We will review your message and respond to you at {{EMAIL}} as soon as possible.</p>
                    <p>Best regards,<br>{{SITE_NAME}} Team</p>
                ',
                'placeholders' => json_encode([
                    '{{NAME}}', '{{EMAIL}}', '{{PHONE}}', '{{MESSAGE}}', '{{SITE_NAME}}', '{{SITE_EMAIL}}', '{{SITE_PHONE}}', '{{SITE_LOGO}}', '{{CONTACT_US_URL}}', '{{CURRENT_YEAR}}'
                ]),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'New Contact Enquiry',
                'subject' => 'New Contact Enquiry from {{SITE_NAME}}',
                'slug' => 'admin_contact_enquiry',
                'body' => '
                    <h1>New Contact Enquiry</h1>
                    <p>A new contact enquiry has been submitted on <strong>{{SITE_NAME}}</strong>.</p>
                    <p><strong>Contact Details:</strong></p>
                    <p><strong>Name:</strong> {{NAME}}</p>
                    <p><strong>Email:</strong> {{EMAIL}}</p>
                    <p><strong>Phone:</strong> {{PHONE}}</p>
                    <p><strong>Message:</strong></p>
                    <p>{{MESSAGE}}</p>
                    <p>Please review and respond to this enquiry at your earliest convenience.</p>
                    <p>You can view all contact enquiries in the admin panel.</p>
                ',
                'placeholders' => json_encode([
                    '{{NAME}}', '{{EMAIL}}', '{{PHONE}}', '{{MESSAGE}}', '{{SITE_NAME}}', '{{SITE_EMAIL}}', '{{SITE_PHONE}}', '{{SITE_LOGO}}', '{{CONTACT_US_URL}}', '{{CURRENT_YEAR}}', '{{RECIPIENT_EMAIL}}', '{{RECIPIENT_NAME}}'
                ]),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'         => 'Booking Confirmation',
                'subject'      => 'Booking Confirmed — {{SERVICE_NAME}} at {{TENANT_NAME}}',
                'slug'         => 'booking_confirmation',
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
                'name'         => 'Booking Cancellation',
                'subject'      => 'Booking Cancelled — {{SERVICE_NAME}}',
                'slug'         => 'booking_cancellation',
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
                'name'         => 'Booking Reminder',
                'subject'      => 'Reminder: {{SERVICE_NAME}} tomorrow',
                'slug'         => 'booking_reminder',
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
                'name'         => 'Provider New Booking',
                'subject'      => 'New Booking — {{SERVICE_NAME}} on {{BOOKING_DATE}}',
                'slug'         => 'provider_new_booking',
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

        DB::table('email_templates')->insertOrIgnore($templates);
    }
}
