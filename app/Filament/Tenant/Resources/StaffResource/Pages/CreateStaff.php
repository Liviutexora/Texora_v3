<?php

namespace App\Filament\Tenant\Resources\StaffResource\Pages;

use App\Filament\Tenant\Resources\StaffResource;
use App\Models\Tenant;
use App\Support\TenantContext;
use Filament\Resources\Pages\CreateRecord;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = TenantContext::id()
            ?? session('impersonate_tenant_id')
            ?? Tenant::where('owner_id', auth()->id())->value('id');

        $data['tenant_id'] = $tenantId;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
