<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // 1. Rename Professional plans → Pro
        DB::table('subscription_plans')
            ->where('slug', 'professional-monthly')
            ->update(['name' => 'Pro (Monthly)', 'updated_at' => $now]);

        DB::table('subscription_plans')
            ->where('slug', 'professional-yearly')
            ->update(['name' => 'Pro (Annually)', 'updated_at' => $now]);

        // 2. Insert Free plan (skip if already exists)
        if (! DB::table('subscription_plans')->where('slug', 'free')->exists()) {
            DB::table('subscription_plans')->insert([
                'name'                   => 'Free',
                'slug'                   => 'free',
                'price'                  => 0.00,
                'billing_cycle'          => 'monthly',
                'sort_order'             => 0,
                'max_providers'          => 1,
                'max_bookings_per_month' => 30,
                'features'               => json_encode([
                    ['icon' => 'tag',      'text' => '1 service'],
                    ['icon' => 'users',    'text' => '1 service provider'],
                    ['icon' => 'calendar', 'text' => 'Up to 30 bookings per month'],
                    ['icon' => 'globe',    'text' => 'Custom branded booking page'],
                    ['icon' => 'mail',     'text' => 'Automated email confirmations'],
                    ['icon' => 'clock',    'text' => 'Real-time availability management'],
                    ['icon' => 'x-circle', 'text' => 'Client self-service cancellation'],
                ]),
                'is_active'              => 1,
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Reverse renames
        DB::table('subscription_plans')
            ->where('slug', 'professional-monthly')
            ->update(['name' => 'Professional (Monthly)']);

        DB::table('subscription_plans')
            ->where('slug', 'professional-yearly')
            ->update(['name' => 'Professional (Annually)']);

        // Remove Free plan (only if it has no tenants on it)
        DB::table('subscription_plans')
            ->where('slug', 'free')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('tenants')
                  ->whereColumn('tenants.plan_id', 'subscription_plans.id');
            })
            ->delete();
    }
};
