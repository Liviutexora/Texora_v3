<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Trial ending email was removed when the trial system was dropped.
// This migration now removes the template from existing installs.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')->where('slug', 'trial_ending')->delete();
    }

    public function down(): void
    {
        // Intentionally empty — no need to restore a removed feature
    }
};
