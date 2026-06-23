<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Illuminate\Support\Facades\Artisan;

class ShieldSeeder extends Seeder
{
    use HasPanelShield;
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        syncModulePermission();
        $rolesWithPermissions = ["name" => "super_admin","guard_name" =>"web"];
        static::makeRolesWithPermissions($rolesWithPermissions);
        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(array $rolesWithPermissions): void
    {
        if ($rolesWithPermissions) {
            $roleModel = Utils::getRoleModel();
            foreach ($rolesWithPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);
                $role->syncPermissions(Permission::all());
            }
        }
    }
}
