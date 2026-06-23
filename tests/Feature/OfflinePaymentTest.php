<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\Service;
use App\Models\SlotReservation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BookingPaymentService;
use App\Support\TenantPaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflinePaymentTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private SlotReservation $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::forceCreate([
            'name' => 'Offline Owner',
            'email' => 'owner-offline@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->tenant = Tenant::create([
            'name' => 'Offline Clinic',
            'slug' => 'offline-clinic',
            'status' => 'active',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'owner_id' => $owner->id,
        ]);

        $service = Service::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Service',
            'duration' => 30,
            'price' => 40,
            'status' => 'active',
        ]);

        $providerUser = User::forceCreate([
            'name' => 'Provider',
            'email' => 'provider-offline@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $provider = Provider::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $providerUser->id,
            'status' => 'active',
        ]);

        $this->booking = SlotReservation::create([
            'tenant_id' => $this->tenant->id,
            'service_id' => $service->id,
            'provider_id' => $provider->id,
            'name' => 'Sam',
            'email' => 'sam@example.com',
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'confirmed',
            'amount' => 40,
            'currency' => 'USD',
            'payment_status' => 'pending',
            'cancellation_token' => (string) Str::uuid(),
        ]);
    }

    public function test_record_cash_payment_marks_booking_paid(): void
    {
        TenantPaymentSettings::for($this->tenant->id)->save([
            'offline_cash_enabled' => true,
        ]);

        app(BookingPaymentService::class)->recordOfflinePayment(
            $this->booking,
            'cash',
            'CASH-001',
        );

        $this->booking->refresh();
        $this->assertSame('paid', $this->booking->payment_status);
        $this->assertSame('cash', $this->booking->payment_gateway);
        $this->assertSame('CASH-001', $this->booking->payment_reference);
    }

    public function test_record_bank_transfer_when_enabled(): void
    {
        TenantPaymentSettings::for($this->tenant->id)->save([
            'offline_bank_transfer_enabled' => true,
        ]);

        app(BookingPaymentService::class)->recordOfflinePayment(
            $this->booking,
            'bank_transfer',
            'WIRE-99',
        );

        $this->booking->refresh();
        $this->assertSame('bank_transfer', $this->booking->payment_gateway);
    }

    public function test_rejects_disabled_offline_method(): void
    {
        TenantPaymentSettings::for($this->tenant->id)->save([
            'offline_cash_enabled' => false,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        app(BookingPaymentService::class)->recordOfflinePayment($this->booking, 'cash');
    }
}
