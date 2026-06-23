<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add sort_order so buyers can control register-page plan ordering
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
        });

        // 2. Set natural order for the two Professional plans
        DB::table('subscription_plans')->where('slug', 'professional-monthly')->update(['sort_order' => 1]);
        DB::table('subscription_plans')->where('slug', 'professional-yearly')->update(['sort_order' => 2]);

        // 3. Re-point any tenants still on old Starter/Pro plans → professional-monthly
        $monthlyId = DB::table('subscription_plans')->where('slug', 'professional-monthly')->value('id');
        if ($monthlyId) {
            $oldIds = DB::table('subscription_plans')
                ->whereIn('slug', ['starter', 'pro'])
                ->pluck('id');

            if ($oldIds->isNotEmpty()) {
                DB::table('tenants')
                    ->whereIn('plan_id', $oldIds)
                    ->update(['plan_id' => $monthlyId]);
            }
        }

        // 4. Remove the old inactive plans entirely
        DB::table('subscription_plans')->whereIn('slug', ['starter', 'pro'])->delete();
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
