<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Prevent delete action for super admin
     */
    protected function getHeaderActions(): array
    {
        $actions = [];

        if ($this->record && ! $this->isSuperAdmin($this->record)) {
            $actions[] = DeleteAction::make();
        }

        return $actions;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles->first()?->id;
        $data['email_verified_at'] = filled($this->record->email_verified_at);
        $data['tenant_id'] = $this->record->tenant_id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['roles']) && ! $this->isSuperAdmin($this->record)) {
            $this->record->syncRoles([$data['roles']]);
        }
        unset($data['roles']);

        return $data;
    }

    /**
     * Check if user is super_admin
     */
    private function isSuperAdmin(Model $record): bool
    {
        return $record->hasRole('super_admin');
    }
}
