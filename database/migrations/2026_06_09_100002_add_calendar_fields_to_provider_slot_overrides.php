<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_slot_overrides', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('status');
            $table->string('external_event_id', 191)->nullable()->after('reservation_id');
            $table->index(['provider_id', 'external_event_id'], 'provider_slot_overrides_external_event_idx');
        });
    }

    public function down(): void
    {
        Schema::table('provider_slot_overrides', function (Blueprint $table) {
            $table->dropIndex('provider_slot_overrides_external_event_idx');
            $table->dropColumn(['reason', 'external_event_id']);
        });
    }
};
