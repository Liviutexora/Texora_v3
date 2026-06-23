<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('email_templates')->insertOrIgnore([
            'name'    => 'Contact Reply',
            'slug'    => 'admin_contact_reply',
            'subject' => 'Re: Your enquiry to {{SITE_NAME}}',
            'body'    => '<p>Hi <strong>{{NAME}}</strong>,</p>
<p>Thank you for reaching out to us. Here is our response to your enquiry:</p>
<blockquote style="border-left:4px solid #7c3aed;padding:0.5rem 1rem;margin:1rem 0;color:#374151;background:#f5f3ff;border-radius:0 6px 6px 0;">
    {{REPLY}}
</blockquote>
<p style="color:#6b7280;font-size:13px;">Your original message:</p>
<blockquote style="border-left:4px solid #d1d5db;padding:0.5rem 1rem;margin:1rem 0;color:#6b7280;font-size:13px;">
    {{MESSAGE}}
</blockquote>
<p>If you have any further questions, feel free to <a href="{{CONTACT_US_URL}}">contact us</a> again.</p>
<p>Best regards,<br><strong>{{SITE_NAME}} Team</strong></p>',
            'placeholders' => json_encode([
                '{{NAME}}', '{{REPLY}}', '{{MESSAGE}}',
                '{{SITE_NAME}}', '{{SITE_EMAIL}}', '{{CONTACT_US_URL}}', '{{CURRENT_YEAR}}',
            ]),
            'is_active'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('slug', 'admin_contact_reply')->delete();
    }
};
