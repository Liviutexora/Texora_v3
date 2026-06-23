<?php

namespace App\Support;

use App\Models\Setting;

class TenantCalendarSettings
{
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
        return (bool) Setting::get($this->key('calendar_sync_enabled'), false);
    }

    public function twoWaySync(): bool
    {
        return (bool) Setting::get($this->key('calendar_two_way_sync'), false);
    }

    public function clientId(): ?string
    {
        return Setting::get('google_calendar_client_id')
            ?: config('services.google.calendar_client_id')
            ?: config('services.google.client_id');
    }

    public function clientSecret(): ?string
    {
        return Setting::get('google_calendar_client_secret')
            ?: config('services.google.calendar_client_secret')
            ?: config('services.google.client_secret');
    }

    public function isOAuthConfigured(): bool
    {
        return $this->clientId() && $this->clientSecret();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(array $data): void
    {
        Setting::set($this->key('calendar_sync_enabled'), ($data['calendar_sync_enabled'] ?? false) ? '1' : '0');
        Setting::set($this->key('calendar_two_way_sync'), ($data['calendar_two_way_sync'] ?? false) ? '1' : '0');
    }

    /**
     * @return array<string, mixed>
     */
    public function toFormData(): array
    {
        return [
            'calendar_sync_enabled' => $this->isEnabled(),
            'calendar_two_way_sync' => $this->twoWaySync(),
            'oauth_configured'      => $this->isOAuthConfigured(),
        ];
    }
}
