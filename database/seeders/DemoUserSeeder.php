<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DemoUserSeeder — intentionally empty for the Booking SaaS product.
 *
 * All demo users (super admin, tenant owners, providers) are created by
 * BookingSaasSeeder.  This file is kept so nothing breaks if it's called
 * from older scripts, but it does nothing.
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // No-op — all users created by BookingSaasSeeder
    }

    /**
     * Called statically by AppServiceProvider in DEMO_MODE.
     * No longer needed — kept to avoid a fatal error.
     */
    public static function ensureDemoUsersExist(): void
    {
        // No-op
    }
}
