<?php

namespace App\Filament\Tenant\Resources\ProviderResource\Pages;

use App\Filament\Tenant\Resources\ProviderResource;
use App\Support\TenantContext;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProvider extends CreateRecord
{
    protected static string $resource = ProviderResource::class;

    /**
     * Override Filament's default abort(403) with a friendly redirect + notification
     * when the tenant has hit their plan's provider limit.
     */
    protected function authorizeAccess(): void
    {
        if (! static::getResource()::canCreate()) {
            $tenant   = TenantContext::current();
            $plan     = $tenant?->plan;
            $limit    = $plan?->max_providers ?? 1;
            $planName = $plan?->name ?? 'your current plan';

            Notification::make()
                ->title(__('Provider limit reached'))
                ->body("{$planName} allows up to {$limit} provider" . ($limit === 1 ? '' : 's') . ". Upgrade to Pro for unlimited providers.")
                ->warning()
                ->persistent()
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));
            return;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = TenantContext::id();

        return $data;
    }
}
