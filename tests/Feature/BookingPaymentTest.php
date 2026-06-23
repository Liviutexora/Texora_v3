<?php

namespace Tests\Feature;

use App\Jobs\SendBookingConfirmationEmail;
use App\Jobs\SendBookingConfirmationSms;
use App\Models\Provider;
use App\Models\Service;
use App\Models\SlotReservation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BookingPaymentService;
use App\Support\TenantPaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Service $service;

    private Provider $provider;

    private SlotReservation $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::forceCreate([
            'name' => 'Payment Owner',
            'email' => 'owner-pay@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->tenant = Tenant::create([
            'name' => 'Payment Clinic',
            'slug' => 'payment-clinic',
            'status' => 'active',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'owner_id' => $owner->id,
        ]);

        $this->service = Service::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Paid Consultation',
            'duration' => 30,
            'price' => 75,
            'status' => 'active',
        ]);

        $providerUser = User::forceCreate([
            'name' => 'Dr Pay',
            'email' => 'dr-pay@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->provider = Provider::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $providerUser->id,
            'status' => 'active',
        ]);

        $this->booking = SlotReservation::create([
            'tenant_id' => $this->tenant->id,
            'service_id' => $this->service->id,
            'provider_id' => $this->provider->id,
            'name' => 'Alex Client',
            'email' => 'alex@example.com',
            'phone' => '+15551234567',
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'confirmed',
            'amount' => 75,
            'currency' => 'USD',
            'payment_status' => 'pending',
            'cancellation_token' => (string) Str::uuid(),
        ]);
    }

    public function test_requires_payment_when_enabled_and_amount_positive(): void
    {
        TenantPaymentSettings::for($this->tenant->id)->save([
            'payment_enabled' => true,
            'require_payment_at_booking' => true,
        ]);

        $service = app(BookingPaymentService::class);

        $this->assertTrue($service->requiresPaymentAtBooking($this->booking));
    }

    public function test_does_not_require_payment_when_disabled(): void
    {
        TenantPaymentSettings::for($this->tenant->id)->save([
            'payment_enabled' => false,
            'require_payment_at_booking' => true,
        ]);

        $service = app(BookingPaymentService::class);

        $this->assertFalse($service->requiresPaymentAtBooking($this->booking));
    }

    public function test_mark_paid_updates_booking_payment_fields(): void
    {
        app(BookingPaymentService::class)->markPaid($this->booking, 'stripe', 'pi_test_123', 'cs_test_456');

        $this->booking->refresh();
        $this->assertSame('paid', $this->booking->payment_status);
        $this->assertSame('stripe', $this->booking->payment_gateway);
        $this->assertSame('pi_test_123', $this->booking->payment_reference);
        $this->assertSame('cs_test_456', $this->booking->checkout_session_id);
        $this->assertNotNull($this->booking->paid_at);
    }

    public function test_dispatch_for_new_booking_queues_confirmation_jobs(): void
    {
        Queue::fake();

        app(\App\Services\BookingNotificationService::class)->dispatchForNewBooking($this->booking);

        Queue::assertPushed(SendBookingConfirmationEmail::class);
        Queue::assertPushed(SendBookingConfirmationSms::class);
    }

    public function test_stripe_webhook_marks_booking_paid_for_payment_checkout(): void
    {
        Queue::fake();

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_booking',
                    'mode' => 'payment',
                    'payment_intent' => 'pi_webhook_1',
                    'client_reference_id' => (string) $this->booking->id,
                    'metadata' => [
                        'type' => 'booking_payment',
                        'booking_id' => $this->booking->id,
                        'tenant_id' => $this->tenant->id,
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/stripe/webhook', $payload);

        $response->assertOk();
        $this->booking->refresh();
        $this->assertSame('paid', $this->booking->payment_status);
        $this->assertSame('stripe', $this->booking->payment_gateway);
    }

    public function test_razorpay_webhook_marks_booking_paid(): void
    {
        Queue::fake();

        $payload = [
            'event' => 'payment_link.paid',
            'payload' => [
                'payment_link' => [
                    'entity' => [
                        'id' => 'plink_test_1',
                        'reference_id' => (string) $this->booking->id,
                    ],
                ],
                'payment' => [
                    'entity' => ['id' => 'pay_test_1'],
                ],
            ],
        ];

        $response = $this->postJson('/razorpay/webhook', $payload);

        $response->assertOk();
        $this->booking->refresh();
        $this->assertSame('paid', $this->booking->payment_status);
        $this->assertSame('razorpay', $this->booking->payment_gateway);
    }

    public function test_paypal_webhook_marks_booking_paid(): void
    {
        Queue::fake();

        $payload = [
            'event_type' => 'CHECKOUT.ORDER.APPROVED',
            'resource' => [
                'id' => 'order_test_1',
                'purchase_units' => [
                    ['custom_id' => (string) $this->booking->id],
                ],
            ],
        ];

        $response = $this->postJson('/paypal/webhook', $payload);

        $response->assertOk();
        $this->booking->refresh();
        $this->assertSame('paid', $this->booking->payment_status);
        $this->assertSame('paypal', $this->booking->payment_gateway);
    }

    public function test_paddle_webhook_marks_booking_paid(): void
    {
        Queue::fake();

        $payload = [
            'event_type' => 'transaction.completed',
            'data' => [
                'id' => 'txn_test_1',
                'custom_data' => [
                    'booking_id' => (string) $this->booking->id,
                ],
            ],
        ];

        $response = $this->postJson('/paddle/webhook', $payload);

        $response->assertOk();
        $this->booking->refresh();
        $this->assertSame('paid', $this->booking->payment_status);
        $this->assertSame('paddle', $this->booking->payment_gateway);
    }

    public function test_payment_success_page_is_accessible_with_token(): void
    {
        $response = $this->get(route('booking.payment.success', [
            'token' => $this->booking->cancellation_token,
        ]));

        $response->assertOk();
        $response->assertSee('Alex Client');
    }

    public function test_receipt_page_is_accessible_with_token(): void
    {
        $this->booking->update(['payment_status' => 'paid', 'paid_at' => now()]);

        $response = $this->get(route('booking.receipt', [
            'token' => $this->booking->cancellation_token,
        ]));

        $response->assertOk();
        $response->assertSee('Paid Consultation');
        $response->assertSee('Alex Client');
    }
}
