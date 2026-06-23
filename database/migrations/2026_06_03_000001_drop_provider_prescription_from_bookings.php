<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slot_reservations', function (Blueprint $table) {
            $table->dropColumn('provider_prescription');
        });
    }

    public function down(): void
    {
        Schema::table('slot_reservations', function (Blueprint $table) {
            $table->text('provider_prescription')->nullable()->after('note');
        });
    }
};
