<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use App\Models\NotificationPreference;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRegistrationEmails implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;
    public $timeout = 120;

    public function __construct(public User $user)
    {
        //
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'registration-emails-' . $this->user->id;
    }

    private const USER_TEMPLATE_SLUG = 'user_welcome';
    private const ADMIN_TEMPLATE_SLUG = 'admin_new_registration';

    public function handle(): void
    {
        $this->configureMailTransport();

        $siteName     = Setting::get('site_name', config('app.name'));
        $contactEmail = Setting::get('contact_email', config('mail.from.address'));
        $loginUrl     = route('login');

        $basePlaceholders = $this->buildPlaceholderMap($siteName, $contactEmail, $loginUrl);

        // Send welcome email to the new user — gated by the welcome_email preference.
        // Default to sending if no preference record exists yet (first-run / opt-out model).
        $welcomePrefs = NotificationPreference::where('permission_name', 'welcome_email')->get();
        $shouldSendWelcome = $welcomePrefs->isEmpty() || $welcomePrefs->contains('email', true);

        if ($shouldSendWelcome) {
            $this->sendUsingTemplate(
                slug: self::USER_TEMPLATE_SLUG,
                placeholders: $basePlaceholders,
                fallbackSubject: 'Welcome to {{SITE_NAME}}, {{NAME}}!',
                fallbackBody: <<<'HTML'
                    <p>Hi {{NAME}},</p>
                    <p>Welcome to {{SITE_NAME}}! We're excited to have you on board.</p>
                    <p>You can sign in anytime using <a href="{{LOGIN_URL}}">this link</a>.</p>
                HTML,
                toEmail: $this->user->email,
                toName: $this->user->name
            );
        }

        // Notify each admin who opted in to new_registration (email and/or web).
        $adminPrefs = NotificationPreference::where('permission_name', 'new_registration')
            ->where(function ($q) {
                $q->where('email', true)->orWhere('web_notification', true);
            })
            ->with('user')
            ->get();

        foreach ($adminPrefs as $pref) {
            if ($pref->email && $pref->user) {
                $adminPlaceholders = array_merge($basePlaceholders, [
                    '{{RECIPIENT_EMAIL}}' => $pref->user->email,
                    '{{RECIPIENT_NAME}}'  => $pref->user->name,
                ]);
                $this->sendUsingTemplate(
                    slug: self::ADMIN_TEMPLATE_SLUG,
                    placeholders: $adminPlaceholders,
                    fallbackSubject: 'New registration on {{SITE_NAME}}',
                    fallbackBody: <<<'HTML'
                        <p>New user {{NAME}} ({{EMAIL}}) has registered on {{SITE_NAME}}.</p>
                    HTML,
                    toEmail: $pref->user->email,
                    toName: $pref->user->name
                );
            }

            if ($pref->web_notification) {
                $url = rescue(fn () => route('filament.admin.resources.users.edit', ['record' => $this->user->id]), null);
                NotificationHelper::send(
                    receiverId: $pref->user_id,
                    heading: 'New User Registration',
                    message: trim($this->user->name.' ('.$this->user->email.')') . ' has just registered.',
                    url: $url,
                );
            }
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
            : asset('storage/' . ltrim($logo, '/'));
    }

    protected function replacePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace($key, (string) $value, $text);
        }

        return $text;
    }

    protected function buildPlaceholderMap(string $siteName, ?string $contactEmail, string $loginUrl): array
    {
        $contactPhone = Setting::get('contact_phone', '');
        $siteLogo = $this->getSiteLogoUrl();
        $currentYear = now()->format('Y');

        return [
            '{{NAME}}' => $this->user->name,
            '{{USER_NAME}}' => $this->user->name,
            '{{EMAIL}}' => $this->user->email,
            '{{USER_EMAIL}}' => $this->user->email,
            '{{SITE_NAME}}' => $siteName,
            '{{SITE_EMAIL}}' => $contactEmail ?? '',
            '{{SITE_PHONE}}' => $contactPhone,
            '{{SITE_LOGO}}' => $siteLogo,
            '{{LOGIN_URL}}' => $loginUrl,
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

