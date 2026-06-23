<?php

namespace App\Filament\Resources\Role\Pages;

use App\Filament\Resources\Role\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public Collection $permissions;

    protected function getActions(): array
    {
        return [
            DeleteAction::make()->visible(fn ($record) => ! in_array($record->name, ['super_admin', 'provider', 'client']))
        ];
    }

    /**
     * Load existing permissions into the form when editing
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure we have a record
        if (!$this->record) {
            return $data;
        }
        
        // Clear permission cache to ensure we get fresh data
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Refresh the record to get latest permissions
        $this->record->refresh();
        $this->record->load('permissions');
        
        // Get all available permissions with the same guard_name
        $allPermissions = Utils::getPermissionModel()::where('guard_name', $this->record->guard_name ?? 'web')
            ->pluck('name')
            ->toArray();
        
        // For super_admin role, show all permissions as checked (bypasses all checks)
        if ($this->record->name === 'super_admin') {
            foreach ($allPermissions as $permission) {
                $data[$permission] = true;
            }
        } else {
            // For other roles, get permissions assigned to this specific role
            $rolePermissions = $this->record->permissions
                ->where('guard_name', $this->record->guard_name ?? 'web')
                ->pluck('name')
                ->toArray();
            
            // Set each permission checkbox based on whether the role has it
            foreach ($allPermissions as $permission) {
                $data[$permission] = in_array($permission, $rolePermissions);
            }
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Shield form uses CheckboxList components: each key (e.g. pages_tab, resources_tab, resource FQCN)
        // has value = array of selected permission names. Flatten all into one list of permission names.
        $excludeKeys = ['name', 'guard_name', 'select_all', Utils::getTenantModelForeignKey()];
        $this->permissions = collect($data)
            ->filter(fn (mixed $value, string $key): bool => ! in_array($key, $excludeKeys, true))
            ->values()
            ->flatten()
            ->filter(fn (mixed $v): bool => is_string($v) && $v !== '')
            ->unique()
            ->values();

        if (Arr::has($data, Utils::getTenantModelForeignKey())) {
            return Arr::only($data, ['name', 'guard_name', Utils::getTenantModelForeignKey()]);
        }

        return Arr::only($data, ['name', 'guard_name']);
    }

    protected function afterSave(): void
    {
        $guardName = $this->record->guard_name ?? 'web';
        $permissionModels = $this->permissions
            ->map(fn (string $permission): \Spatie\Permission\Contracts\Permission => Utils::getPermissionModel()::firstOrCreate(
                ['name' => $permission, 'guard_name' => $guardName]
            ));
        $this->record->syncPermissions($permissionModels);
    }
}
