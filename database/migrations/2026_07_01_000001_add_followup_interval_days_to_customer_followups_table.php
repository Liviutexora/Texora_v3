<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_followups', function (Blueprint $table) {
            $table->unsignedSmallInteger('followup_interval_days')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('customer_followups', function (Blueprint $table) {
            $table->dropColumn('followup_interval_days');
        });
    }
};