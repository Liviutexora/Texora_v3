<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slot_reservations', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
            $table->string('checkout_session_id', 191)->nullable()->after('paid_at');
            $table->string('google_calendar_event_id', 191)->nullable()->after('checkout_session_id');
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->boolean('calendar_sync_enabled')->default(false)->after('is_active');
            $table->string('google_calendar_id')->nullable()->after('calendar_sync_enabled');
            $table->text('google_access_token')->nullable()->after('google_calendar_id');
            $table->text('google_refresh_token')->nullable()->after('google_access_token');
            $table->timestamp('google_token_expires_at')->nullable()->after('google_refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('slot_reservations', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'checkout_session_id', 'google_calendar_event_id']);
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn([
                'calendar_sync_enabled',
                'google_calendar_id',
                'google_access_token',
                'google_refresh_token',
                'google_token_expires_at',
            ]);
        });
    }
};
