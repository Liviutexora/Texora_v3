<?php

namespace App\Support;

use App\Models\Setting;

class TenantSmsSettings
{
    public const TOKEN_PLACEHOLDER = '••••••••';

    public function __construct(public readonly int $tenantId) {}

    public static function for(int $tenantId): self
    {
        return new self($tenantId);
    }

    private function key(string $suffix): string
    {
        return "tenant_{$this->tenantId}_{$suffix}";
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get($this->key('sms_enabled'), false);
    }

    public function accountSid(): ?string
    {
        return Setting::get($this->key('twilio_account_sid')) ?: null;
    }

    public function authToken(): ?string
    {
        $stored = Setting::get($this->key('twilio_auth_token'));

        return $stored ? decrypt($stored) : null;
    }

    public function fromNumber(): ?string
    {
        return Setting::get($this->key('twilio_from_number')) ?: null;
    }

    public function isConfigured(): bool
    {
        return (bool) $this->accountSid()
            && (bool) $this->authToken()
            && (bool) $this->fromNumber();
    }

    public function canSend(): bool
    {
        return $this->isEnabled() && $this->isConfigured();
    }

    public function isConfirmationEnabled(): bool
    {
        return (bool) Setting::get($this->key('sms_confirmation'), false);
    }

    public function isReminderEnabled(): bool
    {
        return (bool) Setting::get($this->key('sms_reminder'), false);
    }

    public function isCancellationEnabled(): bool
    {
        return (bool) Setting::get($this->key('sms_cancellation'), false);
    }

    public function isRescheduledEnabled(): bool
    {
        return (bool) Setting::get($this->key('sms_rescheduled'), false);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(array $data): void
    {
        Setting::set($this->key('sms_enabled'), ($data['sms_enabled'] ?? false) ? '1' : '0');
        Setting::set($this->key('sms_confirmation'), ($data['sms_confirmation'] ?? false) ? '1' : '0');
        Setting::set($this->key('sms_reminder'), ($data['sms_reminder'] ?? false) ? '1' : '0');
        Setting::set($this->key('sms_cancellation'), ($data['sms_cancellation'] ?? false) ? '1' : '0');
        Setting::set($this->key('sms_rescheduled'), ($data['sms_rescheduled'] ?? false) ? '1' : '0');

        if (isset($data['twilio_account_sid'])) {
            Setting::set($this->key('twilio_account_sid'), $data['twilio_account_sid']);
        }

        if (isset($data['twilio_auth_token']) && $data['twilio_auth_token'] !== self::TOKEN_PLACEHOLDER) {
            Setting::set($this->key('twilio_auth_token'), encrypt($data['twilio_auth_token']));
        }

        if (isset($data['twilio_from_number'])) {
            Setting::set($this->key('twilio_from_number'), $data['twilio_from_number']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toFormData(): array
    {
        $hasToken = (bool) Setting::get($this->key('twilio_auth_token'));

        return [
            'sms_enabled'         => $this->isEnabled(),
            'twilio_account_sid'  => $this->accountSid() ?? '',
            'twilio_auth_token'   => $hasToken ? self::TOKEN_PLACEHOLDER : '',
            'twilio_from_number'  => $this->fromNumber() ?? '',
            'sms_confirmation'    => $this->isConfirmationEnabled(),
            'sms_reminder'        => $this->isReminderEnabled(),
            'sms_cancellation'    => $this->isCancellationEnabled(),
            'sms_rescheduled'     => $this->isRescheduledEnabled(),
        ];
    }
}
