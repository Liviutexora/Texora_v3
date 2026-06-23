<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create subscription_plan_prices table ─────────────────────
        Schema::create('subscription_plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')
                  ->constrained('subscription_plans')
                  ->cascadeOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly', 'weekly']);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('stripe_price_id')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['plan_id', 'billing_cycle']);
        });

        // ── 2. Seed prices from every existing plan row ───────────────────
        $plans = DB::table('subscription_plans')->orderBy('id')->get();

        foreach ($plans as $plan) {
            DB::table('subscription_plan_prices')->insertOrIgnore([
                'plan_id'         => $plan->id,
                'billing_cycle'   => $plan->billing_cycle ?? 'monthly',
                'price'           => $plan->price ?? 0,
                'stripe_price_id' => $plan->stripe_price_id ?: null,
                'is_active'       => true,
                'sort_order'      => ($plan->billing_cycle === 'yearly') ? 1 : 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // ── 3. Merge duplicate Pro plans (monthly + yearly) into one row ──
        // Find monthly paid plan (lowest id with price > 0 and billing_cycle = monthly)
        $monthly = DB::table('subscription_plans')
            ->where('price', '>', 0)
            ->where('billing_cycle', 'monthly')
            ->orderBy('id')
            ->first();

        // Find yearly paid plan
        $yearly = DB::table('subscription_plans')
            ->where('price', '>', 0)
            ->where('billing_cycle', 'yearly')
            ->orderBy('id')
            ->first();

        if ($monthly && $yearly && $monthly->id !== $yearly->id) {
            // Add the yearly price entry under the monthly plan (if not already there)
            DB::table('subscription_plan_prices')->insertOrIgnore([
                'plan_id'         => $monthly->id,
                'billing_cycle'   => 'yearly',
                'price'           => $yearly->price,
                'stripe_price_id' => $yearly->stripe_price_id ?: null,
                'is_active'       => true,
                'sort_order'      => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Reassign tenants from the yearly plan to the monthly plan
            DB::table('tenants')
                ->where('plan_id', $yearly->id)
                ->update(['plan_id' => $monthly->id]);

            // Remove price entries that were seeded for the now-merged yearly plan
            DB::table('subscription_plan_prices')
                ->where('plan_id', $yearly->id)
                ->delete();

            // Delete the now-redundant yearly plan row
            DB::table('subscription_plans')
                ->where('id', $yearly->id)
                ->delete();

            // Rename the monthly plan to the base name (strip "(Monthly)" suffix)
            $cleanName = trim(preg_replace('/\s*\(monthly\)/i', '', $monthly->name));
            $cleanSlug = \Illuminate\Support\Str::slug($cleanName);
            DB::table('subscription_plans')
                ->where('id', $monthly->id)
                ->update(['name' => $cleanName, 'slug' => $cleanSlug]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_prices');
    }
};
