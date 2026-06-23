<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('license_number')->nullable();
            $table->text('bio')->nullable();
            $table->string('color', 20)->default('#7c3aed');
            $table->boolean('is_active')->default(true);
            $table->string('job_title')->nullable();
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->timestamps();

            $table->index('user_id', 'doctors_user_id_idx');
            $table->index('tenant_id', 'doctors_tenant_id_index');
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('currency', 10)->default('INR');
            $table->string('color', 20)->default('#6d28d9');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('provider_services', function (Blueprint $table) {
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();

            $table->primary(['provider_id', 'service_id']);
        });

        Schema::create('provider_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('slot_duration_minutes');
            $table->unsignedSmallInteger('buffer_minutes')->default(0);
            $table->time('start_time');
            $table->unsignedSmallInteger('number_of_slots');
            $table->json('available_days');
            $table->timestamps();

            $table->index('tenant_id', 'doctor_shifts_tenant_id_index');
        });

        Schema::create('slot_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 10)->default('INR');
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'waived'])->default('pending');
            $table->string('payment_gateway', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->json('custom_answers')->nullable();
            $table->string('cancellation_token', 64)->unique()->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('gender', 20)->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('note')->nullable();
            $table->text('provider_prescription')->nullable();
            $table->text('provider_note')->nullable();
            $table->string('verification_method', 50)->nullable();
            $table->string('verification_token', 100)->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['provider_id', 'date', 'start_time'], 'unique_slot_booking');
            $table->index(['provider_id', 'date'], 'slot_reservations_doctor_id_date_index');
            $table->index(['provider_id', 'date', 'start_time', 'end_time'], 'slot_reservations_doctor_slot_index');
            $table->index(['provider_id', 'date', 'start_time'], 'slot_reservations_doctor_date_start_idx');
            $table->index('verification_token', 'slot_reservations_verification_token_idx');
            $table->index(['user_id', 'date'], 'slot_reservations_user_date_idx');
            $table->index('created_at', 'slot_reservations_created_at_idx');
            $table->index('status', 'slot_reservations_status_idx');
            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'service_id']);
        });

        // Circular FK: provider_slot_overrides references slot_reservations, so it comes after
        Schema::create('provider_slot_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status');
            $table->foreignId('reservation_id')->nullable()->constrained('slot_reservations')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id', 'doctor_slot_overrides_tenant_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_slot_overrides');
        Schema::dropIfExists('slot_reservations');
        Schema::dropIfExists('provider_shifts');
        Schema::dropIfExists('provider_services');
        Schema::dropIfExists('services');
        Schema::dropIfExists('providers');
    }
};
