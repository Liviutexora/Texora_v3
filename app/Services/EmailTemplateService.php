<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLayout;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    public static function send(string $slug, string $to, array $data = []): bool
    {
        $template = EmailTemplate::where('slug', $slug)->where('is_active', true)->first();

        if (! $template) {
            return false;
        }

        // Replace placeholders in subject and body
        $subject = self::replacePlaceholders($template->subject, $data);
        $body = self::replacePlaceholders($template->body, $data);

        Mail::send([], [], function ($message) use ($to, $subject, $body) {
            $message->to($to)
                ->subject($subject)
                ->html($body);
        });

        return true;
    }

    private static function replacePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace("{{$key}}", $value, $text);
        }

        return $text;
    }

    /**
     * Resolve and render a template (subject + HTML body) without sending.
     * Returns ['subject' => string, 'html' => string].
     */
    public static function renderWithLayoutFallback(
        string $subjectFallback,
        string $bodyFallback,
        array $placeholders = [],
        ?string $templateSlug = null
    ): array {
        $subjectTemplate = $subjectFallback;
        $bodyTemplate    = $bodyFallback;

        if ($templateSlug) {
            $template = EmailTemplate::query()
                ->where('slug', $templateSlug)
                ->where('is_active', true)
                ->first();

            if ($template) {
                $subjectTemplate = $template->subject ?: $subjectFallback;
                $bodyTemplate    = $template->body    ?: $bodyFallback;
            }
        }

        $subject = self::replaceFlexiblePlaceholders($subjectTemplate, $placeholders);
        $body    = self::replaceFlexiblePlaceholders($bodyTemplate, $placeholders);
        $html    = self::applyLayoutOrFallback($body, $placeholders);

        return ['subject' => $subject, 'html' => $html];
    }

    public static function sendWithLayoutFallback(
        string $to,
        string $subjectFallback,
        string $bodyFallback,
        array $placeholders = [],
        ?string $templateSlug = null
    ): bool {
        $subjectTemplate = $subjectFallback;
        $bodyTemplate = $bodyFallback;

        if ($templateSlug) {
            $template = EmailTemplate::query()
                ->where('slug', $templateSlug)
                ->where('is_active', true)
                ->first();

            if ($template) {
                $subjectTemplate = $template->subject ?: $subjectFallback;
                $bodyTemplate = $template->body ?: $bodyFallback;
            }
        }

        $subject = self::replaceFlexiblePlaceholders($subjectTemplate, $placeholders);
        $body = self::replaceFlexiblePlaceholders($bodyTemplate, $placeholders);
        $html = self::applyLayoutOrFallback($body, $placeholders);

        Mail::send([], [], function ($message) use ($to, $subject, $html) {
            $message->to($to)
                ->subject($subject)
                ->html($html);
        });

        return true;
    }

    private static function replaceFlexiblePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace('{{'.$key.'}}', (string) $value, $text);
            $text = str_replace('{'.$key.'}', (string) $value, $text);
        }

        return $text;
    }

    private static function applyLayoutOrFallback(string $body, array $placeholders): string
    {
        $layout = EmailTemplateLayout::query()->where('is_active', true)->first();
        $layoutHtml = $layout?->body ?: self::fallbackLayout();

        if (! str_contains($layoutHtml, '{{BODY}}')) {
            $layoutHtml = self::fallbackLayout();
        }

        $html = str_replace('{{BODY}}', $body, $layoutHtml);

        return self::replaceFlexiblePlaceholders($html, array_merge(self::defaultPlaceholders(), $placeholders));
    }

    private static function defaultPlaceholders(): array
    {
        return [
            'SITE_NAME' => Setting::get('site_name', config('app.name')),
            'SITE_EMAIL' => Setting::get('contact_email', config('mail.from.address')),
            'SITE_PHONE' => Setting::get('contact_phone', ''),
            'CURRENT_YEAR' => now()->format('Y'),
            'SITE_LOGO' => self::siteLogoUrl(),
        ];
    }

    private static function siteLogoUrl(): string
    {
        $logo = Setting::get('site_logo');

        if (! $logo) {
            return asset('logo/logo.png');
        }

        return str_starts_with($logo, 'http') ? $logo : asset('storage/'.ltrim($logo, '/'));
    }

    private static function fallbackLayout(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{SITE_NAME}}</title>
    <style>
        body{margin:0;padding:0;background:#f3f6fb;font-family:Arial,sans-serif;color:#1f2937}
        .wrap{max-width:640px;margin:24px auto;padding:0 12px}
        .card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,.08)}
        .head{padding:24px;background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff;text-align:center}
        .head img{max-height:44px;display:block;margin:0 auto 8px}
        .body{padding:24px;line-height:1.65}
        .foot{padding:16px 24px;background:#f8fafc;border-top:1px solid #e5e7eb;font-size:12px;color:#64748b;text-align:center}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="head">
                <img src="{{SITE_LOGO}}" alt="{{SITE_NAME}} logo">
                <h2 style="margin:0">{{SITE_NAME}}</h2>
            </div>
            <div class="body">{{BODY}}</div>
            <div class="foot">&copy; {{CURRENT_YEAR}} {{SITE_NAME}} | {{SITE_EMAIL}} {{SITE_PHONE}}</div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
