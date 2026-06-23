<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLayout;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendForgotPasswordEmail implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;
    public $timeout = 120;

    public function __construct(
        public User $user,
        public string $resetLink
    ) {
        //
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'forgot-password-email-' . $this->user->id . '-' . time();
    }

    private const TEMPLATE_SLUG = 'forgot_password';

    public function handle(): void
    {
        $this->configureMailTransport();

        $siteName = Setting::get('site_name', config('app.name'));
        $contactEmail = Setting::get('contact_email', config('mail.from.address'));

        $placeholders = $this->buildPlaceholderMap($siteName, $contactEmail);

        $this->sendUsingTemplate(
            slug: self::TEMPLATE_SLUG,
            placeholders: $placeholders,
            fallbackSubject: 'Password Reset Request',
            fallbackBody: <<<'HTML'
                <p>Hello {{NAME}},</p>
                <p>We received a request to reset your password at {{SITE_NAME}}.</p>
                <p>If this was you, please click the link below:</p>
                <a href="{{RESET_LINK}}">Reset Password</a>
                <p>If you did not request this, you can safely ignore this email.</p>
            HTML,
            toEmail: $this->user->email,
            toName: $this->user->name
        );
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
            : asset('storage/' . ltrim($logo, '/'));
    }

    protected function replacePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace($key, (string) $value, $text);
        }

        return $text;
    }

    protected function buildPlaceholderMap(string $siteName, ?string $contactEmail): array
    {
        $contactPhone = Setting::get('contact_phone', '');
        $siteLogo = $this->getSiteLogoUrl();
        $currentYear = now()->format('Y');
        $loginUrl = route('login');

        return [
            '{{NAME}}' => $this->user->name,
            '{{EMAIL}}' => $this->user->email,
            '{{SITE_NAME}}' => $siteName,
            '{{SITE_EMAIL}}' => $contactEmail ?? '',
            '{{SITE_PHONE}}' => $contactPhone,
            '{{SITE_LOGO}}' => $siteLogo,
            '{{RESET_LINK}}' => $this->resetLink,
            '{{LOGIN_URL}}' => $loginUrl,
            '{{CURRENT_YEAR}}' => $currentYear,
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

        if (!$mailHost || !$mailPort || !$mailUsername || !$mailPassword) {
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

