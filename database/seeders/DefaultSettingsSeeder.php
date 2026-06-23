<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── General & SEO ────────────────────────────────────────────────
            ['key' => 'site_name',        'value' => 'Slotara'],
            ['key' => 'site_tagline',     'value' => 'Multi-tenant booking SaaS for salons, gyms, clinics & more.'],
            ['key' => 'site_description', 'value' => 'Slotara lets any business go live with appointment booking in minutes. Copy one line of JavaScript onto any website — done.'],
            ['key' => 'site_keywords',    'value' => 'booking,appointments,saas,slotara,salons,gyms,clinics,online-booking'],
            ['key' => 'site_author',      'value' => 'Slotara'],
            ['key' => 'meta_robots',      'value' => 'index, follow'],

            // ── User management ──────────────────────────────────────────────
            ['key' => 'allow_registration',        'value' => '1'],
            ['key' => 'default_role',               'value' => 'staff'],
            ['key' => 'maintenance_mode',           'value' => '0'],
            ['key' => 'maintenance_message',        'value' => null],
            ['key' => 'docs_enabled',               'value' => '1'],
            ['key' => 'require_email_verification', 'value' => '0'],

            // ── Assets & UI ──────────────────────────────────────────────────
            ['key' => 'site_logo',              'value' => null],
            ['key' => 'site_favicon',           'value' => null],
            ['key' => 'site_logo_height',       'value' => '3'],
            ['key' => 'site_admin_logo_height', 'value' => '3'],
            ['key' => 'site_footer_text',       'value' => '© ' . date('Y') . ' Slotara. All rights reserved.'],

            // ── Contact ──────────────────────────────────────────────────────
            ['key' => 'contact_email',        'value' => 'hello@slotara.app'],
            ['key' => 'contact_phone',        'value' => ''],
            ['key' => 'contact_address',      'value' => ''],
            ['key' => 'contact_office_hours', 'value' => ''],
            ['key' => 'google_maps_embed_code', 'value' => null],
            ['key' => 'contact_city',         'value' => ''],
            ['key' => 'contact_state',        'value' => ''],
            ['key' => 'contact_zip',          'value' => ''],
            ['key' => 'contact_country',      'value' => ''],

            // ── Contact form fields ──────────────────────────────────────────
            ['key' => 'contact_form_fields', 'value' => json_encode([
                ['name' => 'name',    'label' => 'Name',    'type' => 'text',     'enabled' => true, 'required' => true,  'validation' => 'max:255', 'placeholder' => 'Your name',    'rows' => 1],
                ['name' => 'email',   'label' => 'Email',   'type' => 'email',    'enabled' => true, 'required' => true,  'validation' => 'max:255', 'placeholder' => 'you@email.com', 'rows' => 1],
                ['name' => 'phone',   'label' => 'Phone',   'type' => 'tel',      'enabled' => true, 'required' => false, 'validation' => 'max:50',  'placeholder' => '+1 555 000 0000', 'rows' => 1],
                ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'enabled' => true, 'required' => true,  'validation' => '',        'placeholder' => 'How can we help?', 'rows' => 4],
            ])],

            // ── Social media ─────────────────────────────────────────────────
            ['key' => 'social_facebook',  'value' => 'slotara'],
            ['key' => 'social_twitter',   'value' => 'slotara'],
            ['key' => 'social_instagram', 'value' => 'slotara'],
            ['key' => 'social_linkedin',  'value' => 'company/slotara'],
            ['key' => 'social_youtube',   'value' => ''],
            ['key' => 'social_github',    'value' => ''],

            // ── Social login ─────────────────────────────────────────────────
            ['key' => 'google_login_enabled',   'value' => '0'],
            ['key' => 'google_client_id',       'value' => null],
            ['key' => 'google_client_secret',   'value' => null],
            ['key' => 'google_redirect_uri',    'value' => '/auth/google/callback'],
            ['key' => 'facebook_login_enabled', 'value' => '0'],
            ['key' => 'facebook_client_id',     'value' => null],
            ['key' => 'facebook_client_secret', 'value' => null],
            ['key' => 'facebook_redirect_uri',  'value' => '/auth/facebook/callback'],

            // ── reCAPTCHA ────────────────────────────────────────────────────
            ['key' => 'google_recaptcha_enabled',    'value' => '0'],
            ['key' => 'google_recaptcha_site_key',   'value' => null],
            ['key' => 'google_recaptcha_secret_key', 'value' => null],
            ['key' => 'google_recaptcha_verify_url', 'value' => 'https://www.google.com/recaptcha/api/siteverify'],

            // ── Analytics ────────────────────────────────────────────────────
            ['key' => 'google_analytics_id',   'value' => null],
            ['key' => 'google_tag_manager_id', 'value' => null],
            ['key' => 'facebook_pixel_id',     'value' => null],

            // ── Time & locale ────────────────────────────────────────────────
            ['key' => 'timezone',    'value' => 'UTC'],
            ['key' => 'date_format', 'value' => 'Y-m-d'],
            ['key' => 'time_format', 'value' => 'H:i'],

            // ── Languages ────────────────────────────────────────────────────
            ['key' => 'enable_multi_languages', 'value' => '0'],

            // ── Legal ────────────────────────────────────────────────────────
            ['key' => 'cookie_consent_required', 'value' => '0'],
            ['key' => 'terms_of_service_url',    'value' => null],
            ['key' => 'privacy_policy_url',      'value' => null],
            ['key' => 'gdpr_compliance_enabled', 'value' => '0'],
            ['key' => 'data_retention_days',     'value' => '365'],

            // ── Mail ─────────────────────────────────────────────────────────
            ['key' => 'mail_driver',       'value' => 'smtp'],
            ['key' => 'mail_host',         'value' => null],
            ['key' => 'mail_port',         'value' => '587'],
            ['key' => 'mail_username',     'value' => null],
            ['key' => 'mail_password',     'value' => null],
            ['key' => 'mail_encryption',   'value' => 'tls'],
            ['key' => 'mail_from_address', 'value' => 'hello@slotara.app'],
            ['key' => 'mail_from_name',    'value' => 'Slotara'],

            // ── OpenAI ───────────────────────────────────────────────────────
            ['key' => 'openai_api_key', 'value' => null],
            ['key' => 'openai_model',   'value' => 'gpt-4o-mini'],

            // ── Storage & API ────────────────────────────────────────────────
            ['key' => 'storage_driver',      'value' => 'local'],
            ['key' => 'api_rate_limit',      'value' => '60'],
            ['key' => 'api_rate_limit_per',  'value' => '1'],
        ];

        $now = now()->toDateTimeString();

        foreach ($settings as $setting) {
            // Raw DB insert so demo-mode Eloquent save-blocker is bypassed.
            DB::table('settings')->insertOrIgnore([
                'key'        => $setting['key'],
                'value'      => $setting['value'] ?? null,
                'group'      => 'general',
                'type'       => 'text',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
