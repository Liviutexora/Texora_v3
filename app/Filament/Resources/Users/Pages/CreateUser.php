<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var int|string|null */
    protected $pendingRoleId = null;

    protected function authorizeAccess(): void
    {
        if (! auth()->user()->hasRole('super_admin')) {
            abort(403, 'You are not authorized to create users.');
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['roles'])) {
            $this->pendingRoleId = $data['roles'];
            unset($data['roles']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->pendingRoleId !== null && $this->record !== null) {
            $this->record->syncRoles([$this->pendingRoleId]);
        }
    }
}
