<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Spatie roles must exist before any User::factory()->create() call,
        // because UserFactory::afterCreating() calls $user->assignRole('user').
        // Guard with hasTable so tests without RefreshDatabase (ExampleTest) don't
        // blow up when the in-memory SQLite hasn't been migrated yet.
        if (Schema::hasTable('roles')) {
            foreach (['super_admin', 'tenant_owner', 'staff', 'provider', 'client'] as $role) {
                DB::table('roles')->insertOrIgnore([
                    'name'       => $role,
                    'guard_name' => 'web',
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ]);
            }
        }
    }
}
