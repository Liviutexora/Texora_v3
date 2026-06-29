<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('slot_reservation_id')->constrained('slot_reservations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('type');
            $table->string('channel');
            $table->string('status');
            $table->string('priority');
            $table->timestamp('scheduled_at');
            $table->timestamp('next_followup_at')->nullable();
            $table->string('last_action')->nullable();
            $table->timestamp('last_action_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('token')->unique();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('slot_reservation_id');
            $table->index('user_id');
            $table->index('service_id');
            $table->index('provider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_followups');
    }
};
