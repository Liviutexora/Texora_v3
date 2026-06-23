<?php

namespace App\Jobs;

use App\Models\ContactUs;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLayout;
use App\Models\NotificationPreference;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendContactUsReplyEmail implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;
    public $timeout = 120;

    public function __construct(public ContactUs $contactUs) {}

    public function uniqueId(): string
    {
        return 'contact-us-reply-' . $this->contactUs->id . '-' . ($this->contactUs->replied_at?->timestamp ?? time());
    }

    private const TEMPLATE_SLUG = 'admin_contact_reply';

    public function handle(): void
    {
        try {
            if (! NotificationPreference::isEmailEnabled('admin_contact_reply')) {
                return;
            }

            $this->configureMailTransport();

            $toEmail = trim((string) ($this->contactUs->email ?? ''));
            if ($toEmail === '') {
                return;
            }

            $siteName     = Setting::get('site_name', config('app.name'));
            $contactEmail = Setting::get('contact_email', config('mail.from.address'));
            $contactUsUrl = route('contact');

            $placeholders = $this->buildPlaceholderMap($siteName, $contactEmail, $contactUsUrl);

            $this->sendUsingTemplate(
                slug: self::TEMPLATE_SLUG,
                placeholders: $placeholders,
                fallbackSubject: 'Re: Your enquiry to {{SITE_NAME}}',
                fallbackBody: <<<'HTML'
                    <p>Hi {{NAME}},</p>
                    <p>Thank you for reaching out to us. Here is our response to your enquiry:</p>
                    <blockquote style="border-left:4px solid #7c3aed;padding-left:1rem;margin:1rem 0;color:#374151;">
                        {{REPLY}}
                    </blockquote>
                    <p>Your original message:</p>
                    <blockquote style="border-left:4px solid #d1d5db;padding-left:1rem;margin:1rem 0;color:#6b7280;">
                        {{MESSAGE}}
                    </blockquote>
                    <p>Best regards,<br>{{SITE_NAME}} Team</p>
                HTML,
                toEmail: $toEmail,
                toName: $this->contactUs->name
            );
        } catch (\Throwable $e) {
            Log::error('SendContactUsReplyEmail failed', [
                'contact_us_id' => $this->contactUs->id ?? null,
                'message'       => $e->getMessage(),
            ]);
        }
    }

    protected function sendUsingTemplate(
        string $slug,
        array $placeholders,
        string $fallbackSubject,
        string $fallbackBody,
        string $toEmail,
        ?string $toName = null
    ): void {
        [$subject, $body] = $this->buildEmailContent($slug, $placeholders, $fallbackSubject, $fallbackBody);
        Mail::send([], [], function ($message) use ($toEmail, $toName, $subject, $body) {
            $message->to($toEmail, $toName);
            $message->subject($subject);
            $message->html($body);
        });
    }

    protected function buildEmailContent(string $slug, array $placeholders, string $fallbackSubject, string $fallbackBody): array
    {
        $template = EmailTemplate::where('slug', $slug)->where('is_active', true)->first();

        $subject = $this->replacePlaceholders($template ? $template->subject : $fallbackSubject, $placeholders);
        $body    = $this->replacePlaceholders($this->applyLayout($template ? $template->body : $fallbackBody), $placeholders);

        return [$subject, $body];
    }

    protected function applyLayout(string $body): string
    {
        $layout = EmailTemplateLayout::where('is_active', true)->first();
        if (! $layout) {
            return $body;
        }
        $layoutHtml = $layout->body;
        return str_contains($layoutHtml, '{{BODY}}') ? str_replace('{{BODY}}', $body, $layoutHtml) : $body;
    }

    protected function getSiteLogoUrl(): string
    {
        $logo = Setting::get('site_logo');
        if (! $logo) {
            return asset('logo/logo.png');
        }
        return str_starts_with($logo, 'http') ? $logo : asset('storage/' . ltrim($logo, '/'));
    }

    protected function replacePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace($key, (string) $value, $text);
        }
        return $text;
    }

    protected function buildPlaceholderMap(string $siteName, ?string $contactEmail, string $contactUsUrl): array
    {
        return [
            '{{NAME}}'           => $this->contactUs->name ?? '',
            '{{EMAIL}}'          => $this->contactUs->email ?? '',
            '{{PHONE}}'          => $this->contactUs->phone ?? '',
            '{{TYPE}}'           => $this->contactUs->type
                                        ? (\App\Models\ContactUs::TYPE_LIST[$this->contactUs->type] ?? $this->contactUs->type)
                                        : 'General Inquiry',
            '{{MESSAGE}}'        => nl2br(e((string) ($this->contactUs->message ?? ''))),
            '{{REPLY}}'          => nl2br(e((string) ($this->contactUs->admin_reply ?? ''))),
            '{{SITE_NAME}}'      => $siteName,
            '{{SITE_EMAIL}}'     => $contactEmail ?? '',
            '{{SITE_LOGO}}'      => $this->getSiteLogoUrl(),
            '{{CONTACT_US_URL}}' => $contactUsUrl,
            '{{CURRENT_YEAR}}'   => now()->format('Y'),
            '{{RECIPIENT_EMAIL}}' => $this->contactUs->email ?? '',
            '{{RECIPIENT_NAME}}' => $this->contactUs->name ?? '',
        ];
    }

    protected function configureMailTransport(): void
    {
        $mailHost       = Setting::get('mail_host');
        $mailPort       = Setting::get('mail_port');
        $mailUsername   = Setting::get('mail_username');
        $mailPassword   = Setting::get('mail_password');
        $mailEncryption = Setting::get('mail_encryption', 'tls');
        $mailFromAddress = Setting::get('mail_from_address');
        $mailFromName   = Setting::get('mail_from_name');

        if (! $mailHost || ! $mailPort || ! $mailUsername || ! $mailPassword) {
            return;
        }

        config()->set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'host'       => $mailHost,
            'port'       => $mailPort,
            'username'   => $mailUsername,
            'password'   => $mailPassword,
            'encryption' => $mailEncryption ?: null,
        ]);

        if ($mailFromAddress) {
            config()->set('mail.from.address', $mailFromAddress);
        }
        if ($mailFromName) {
            config()->set('mail.from.name', $mailFromName);
        }
    }
}
