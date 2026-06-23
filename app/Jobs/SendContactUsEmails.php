<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
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

class SendContactUsEmails implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;

    public $timeout = 120;

    public function __construct(public ContactUs $contactUs)
    {
        //
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'contact-us-emails-'.$this->contactUs->id;
    }

    private const USER_TEMPLATE_SLUG = 'user_contact_confirmation';

    private const ADMIN_TEMPLATE_SLUG = 'admin_contact_enquiry';

    public function handle(): void
    {
        try {
            $this->configureMailTransport();

            $siteName = Setting::get('site_name', config('app.name'));
            $contactEmail = Setting::get('contact_email', config('mail.from.address'));
            $contactUsUrl = route('contact');

            $basePlaceholders = $this->buildPlaceholderMap($siteName, $contactEmail, $contactUsUrl);
            // Send confirmation email to user (if this email type is enabled)
            $userEmail = trim((string) ($this->contactUs->email ?? ''));
            if ($userEmail !== '' && NotificationPreference::isEmailEnabled('user_contact_confirmation')) {
                $this->sendUsingTemplate(
                    slug: self::USER_TEMPLATE_SLUG,
                    placeholders: $basePlaceholders,
                    fallbackSubject: 'Thank you for contacting {{SITE_NAME}}',
                    fallbackBody: <<<'HTML'
                <h1>Thank You for Contacting Us, {{NAME}}!</h1>
                <p>We have received your message and will get back to you soon.</p>
                <p><strong>Your Message:</strong></p>
                <p>{{MESSAGE}}</p>
                <p>Best regards,<br>{{SITE_NAME}} Team</p>
            HTML,
                    toEmail: $userEmail,
                    toName: $this->contactUs->name
                );
            }

            // Notify each admin who opted in to new_contact_enquiry (email and/or web).
            // No hardcoded always-send — all admin notifications are preference-gated.
            $adminPrefs = NotificationPreference::where('permission_name', 'new_contact_enquiry')
                ->where(function ($q) {
                    $q->where('email', true)->orWhere('web_notification', true);
                })
                ->with('user')
                ->get();

            $label = trim(($this->contactUs->name ?: 'Someone').' ('.($this->contactUs->email ?: 'no email').')');

            foreach ($adminPrefs as $pref) {
                if ($pref->email && $pref->user) {
                    $adminPlaceholders = array_merge($basePlaceholders, [
                        '{{RECIPIENT_EMAIL}}' => $pref->user->email,
                        '{{RECIPIENT_NAME}}'  => $pref->user->name,
                    ]);
                    $this->sendUsingTemplate(
                        slug: self::ADMIN_TEMPLATE_SLUG,
                        placeholders: $adminPlaceholders,
                        fallbackSubject: 'New Contact Enquiry from {{SITE_NAME}}',
                        fallbackBody: <<<'HTML'
                            <h1>New Contact Enquiry</h1>
                            <p>A new contact enquiry has been submitted on <strong>{{SITE_NAME}}</strong>.</p>
                            <p><strong>Name:</strong> {{NAME}}</p>
                            <p><strong>Email:</strong> {{EMAIL}}</p>
                            <p><strong>Phone:</strong> {{PHONE}}</p>
                            <p><strong>Message:</strong></p>
                            <p>{{MESSAGE}}</p>
                        HTML,
                        toEmail: $pref->user->email,
                        toName: $pref->user->name
                    );
                }

                if ($pref->web_notification) {
                    $url = rescue(fn () => route('filament.admin.resources.contact-us.edit', ['record' => $this->contactUs->id]), null);
                    NotificationHelper::send(
                        receiverId: $pref->user_id,
                        heading: 'New Contact Form Submission',
                        message: 'New message from '.$label,
                        url: $url,
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::error('SendContactUsEmails failed', [
                'contact_us_id' => $this->contactUs->id ?? null,
                'message' => $e->getMessage(),
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
        $template = EmailTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->first();



        $subjectTemplate = $template
            ? $template->subject
            : $fallbackSubject;

        $bodyTemplate = $template
            ? $template->body
            : $fallbackBody;

        $subject = $this->replacePlaceholders($subjectTemplate, $placeholders);

        $bodyWithLayout = $this->applyLayout($bodyTemplate);
        $body = $this->replacePlaceholders($bodyWithLayout, $placeholders);

        return [$subject, $body];
    }

    protected function applyLayout(string $body): string
    {
        $layout = EmailTemplateLayout::where('is_active', true)->first();

        if (! $layout) {
            return $body;
        }

        $layoutHtml = $layout->body;

        return str_contains($layoutHtml, '{{BODY}}')
            ? str_replace('{{BODY}}', $body, $layoutHtml)
            : $body;
    }

    protected function getSiteLogoUrl(): string
    {
        $logo = Setting::get('site_logo');

        if (! $logo) {
            return asset('logo/logo.png');
        }

        return str_starts_with($logo, 'http')
            ? $logo
            : asset('storage/'.ltrim($logo, '/'));
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
        $contactPhone = Setting::get('contact_phone', '');
        $siteLogo = $this->getSiteLogoUrl();
        $currentYear = now()->format('Y');

        return [
            '{{NAME}}' => $this->contactUs->name,
            '{{EMAIL}}' => $this->contactUs->email,
            '{{PHONE}}' => $this->contactUs->phone,
            '{{TYPE}}' => $this->contactUs->type ? (\App\Models\ContactUs::TYPE_LIST[$this->contactUs->type] ?? $this->contactUs->type) : 'General Inquiry',
            '{{MESSAGE}}' => $this->contactUs->message,
            '{{SITE_NAME}}' => $siteName,
            '{{SITE_EMAIL}}' => $contactEmail ?? '',
            '{{SITE_PHONE}}' => $contactPhone,
            '{{SITE_LOGO}}' => $siteLogo,
            '{{CONTACT_US_URL}}' => $contactUsUrl,
            '{{CURRENT_YEAR}}' => $currentYear,
            '{{RECIPIENT_EMAIL}}' => $contactEmail ?? '',
            '{{RECIPIENT_NAME}}' => $siteName,
        ];
    }

    protected function configureMailTransport(): void
    {
        $mailHost = Setting::get('mail_host');
        $mailPort = Setting::get('mail_port');
        $mailUsername = Setting::get('mail_username');
        $mailPassword = Setting::get('mail_password');
        $mailEncryption = Setting::get('mail_encryption', 'tls');
        $mailFromAddress = Setting::get('mail_from_address');
        $mailFromName = Setting::get('mail_from_name');

        if (! $mailHost || ! $mailPort || ! $mailUsername || ! $mailPassword) {
            return;
        }

        $config = [
            'transport' => 'smtp',
            'host' => $mailHost,
            'port' => $mailPort,
            'username' => $mailUsername,
            'password' => $mailPassword,
            'encryption' => $mailEncryption ?: null,
        ];
        config()->set('mail.mailers.smtp', $config);

        if ($mailFromAddress) {
            config()->set('mail.from.address', $mailFromAddress);
        }

        if ($mailFromName) {
            config()->set('mail.from.name', $mailFromName);
        }
    }
}
