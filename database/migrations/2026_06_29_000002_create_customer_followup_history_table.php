<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_followup_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('followup_id')->constrained('customer_followups')->cascadeOnDelete();
            $table->string('action');
            $table->string('channel');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('followup_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_followup_history');
    }
};
