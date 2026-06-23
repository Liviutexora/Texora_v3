<?php

namespace App\Services\PaymentGateways;

use App\Contracts\BookingPaymentGateway;
use App\Models\SlotReservation;
use App\Services\BookingPaymentService;
use App\Support\TenantPaymentSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalBookingGateway implements BookingPaymentGateway
{
    public function name(): string
    {
        return 'paypal';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function createCheckoutUrl(SlotReservation $booking, string $successUrl, string $cancelUrl): ?string
    {
        $settings = TenantPaymentSettings::for($booking->tenant_id);

        if (! $settings->isGatewayConfigured('paypal')) {
            return null;
        }

        $token = $this->accessToken($settings);
        if (! $token) {
            return null;
        }

        $base = $settings->paypalMode() === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $amount = number_format((float) $booking->amount, 2, '.', '');
        $currency = strtoupper($booking->currency ?? 'USD');

        try {
            $response = Http::withToken($token)
                ->post("{$base}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => (string) $booking->id,
                        'custom_id'    => (string) $booking->id,
                        'amount'       => [
                            'currency_code' => $currency,
                            'value'         => $amount,
                        ],
                        'description' => $booking->service?->name ?? __('Booking'),
                    ]],
                    'application_context' => [
                        'return_url' => $successUrl,
                        'cancel_url' => $cancelUrl,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('PayPalBookingGateway: order create failed', [
                    'booking_id' => $booking->id,
                    'body'       => $response->body(),
                ]);

                return null;
            }

            $orderId = $response->json('id');
            $approveLink = collect($response->json('links', []))
                ->firstWhere('rel', 'approve')['href'] ?? null;

            if ($orderId) {
                $booking->update(['checkout_session_id' => $orderId]);
            }

            return $approveLink;
        } catch (\Throwable $e) {
            Log::error('PayPalBookingGateway: exception', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function handleWebhook(array $payload): ?SlotReservation
    {
        $eventType = $payload['event_type'] ?? '';
        if ($eventType !== 'CHECKOUT.ORDER.APPROVED' && $eventType !== 'PAYMENT.CAPTURE.COMPLETED') {
            return null;
        }

        $resource = $payload['resource'] ?? [];
        $bookingId = $resource['purchase_units'][0]['custom_id']
            ?? $resource['purchase_units'][0]['reference_id']
            ?? null;

        if (! $bookingId) {
            return null;
        }

        $booking = SlotReservation::withoutGlobalScope('tenant')->find($bookingId);
        if (! $booking) {
            return null;
        }

        app(BookingPaymentService::class)->markPaid(
            $booking,
            'paypal',
            $resource['id'] ?? null,
            $resource['id'] ?? null,
        );

        return $booking->fresh();
    }

    private function accessToken(TenantPaymentSettings $settings): ?string
    {
        $cacheKey = "paypal_token_{$settings->tenantId}_{$settings->paypalMode()}";

        return Cache::remember($cacheKey, 3000, function () use ($settings) {
            $base = $settings->paypalMode() === 'live'
                ? 'https://api-m.paypal.com'
                : 'https://api-m.sandbox.paypal.com';

            $response = Http::asForm()
                ->withBasicAuth($settings->paypalClientId(), $settings->paypalClientSecret())
                ->post("{$base}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if (! $response->successful()) {
                return null;
            }

            return $response->json('access_token');
        });
    }
}
