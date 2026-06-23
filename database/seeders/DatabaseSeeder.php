<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Roles ─────────────────────────────────────────────────────────
        // Use raw DB inserts so demo-mode's Eloquent save-blocker doesn't
        // prevent the Spatie Role model from persisting.
        $now = now()->toDateTimeString();
        foreach (['super_admin', 'tenant_owner', 'staff', 'client'] as $role) {
            DB::table('roles')->insertOrIgnore([
                'name'       => $role,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ── 2. Permissions (none defined for now) ────────────────────────────
        syncModulePermission();
        $this->call(PermissionsSeeder::class);

        // ── 3. App settings ──────────────────────────────────────────────────
        $this->call(DefaultSettingsSeeder::class);

        // ── 4. Email templates ───────────────────────────────────────────────
        $this->call(EmailTemplateLayoutSeeder::class);
        $this->call(EmailTemplateSeeder::class);

    }
}
