<?php

namespace App\Filament\Tenant\Resources\ServiceResource\Pages;

use App\Filament\Tenant\Resources\ServiceResource;
use App\Support\TenantContext;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    /**
     * Override Filament's default abort(403) with a friendly redirect + notification
     * when the tenant has hit their plan's service limit.
     */
    protected function authorizeAccess(): void
    {
        if (! static::getResource()::canCreate()) {
            $tenant   = TenantContext::current();
            $plan     = $tenant?->plan;
            $limit    = $plan?->max_services ?? 1;
            $planName = $plan?->name ?? 'your current plan';

            Notification::make()
                ->title(__('Service limit reached'))
                ->body("{$planName} allows up to {$limit} service" . ($limit === 1 ? '' : 's') . ". Upgrade to Pro for unlimited services.")
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
