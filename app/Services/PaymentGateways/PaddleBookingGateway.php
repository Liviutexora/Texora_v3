<?php

namespace App\Services\PaymentGateways;

use App\Contracts\BookingPaymentGateway;
use App\Models\SlotReservation;
use App\Services\BookingPaymentService;
use App\Support\TenantPaymentSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaddleBookingGateway implements BookingPaymentGateway
{
    public function name(): string
    {
        return 'paddle';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function createCheckoutUrl(SlotReservation $booking, string $successUrl, string $cancelUrl): ?string
    {
        $settings = TenantPaymentSettings::for($booking->tenant_id);

        if (! $settings->isGatewayConfigured('paddle')) {
            return null;
        }

        $apiKey = $settings->paddleApiKey();
        $vendorId = $settings->paddleVendorId();

        try {
            $response = Http::withToken($apiKey)
                ->post('https://api.paddle.com/transactions', [
                    'items' => [[
                        'price' => [
                            'description' => $booking->service?->name ?? __('Booking'),
                            'unit_price'  => [
                                'amount'        => (string) ((int) round((float) $booking->amount * 100)),
                                'currency_code' => strtoupper($booking->currency ?? 'USD'),
                            ],
                            'quantity' => 1,
                        ],
                    ]],
                    'custom_data' => [
                        'booking_id' => (string) $booking->id,
                        'tenant_id'  => (string) $booking->tenant_id,
                    ],
                    'checkout' => [
                        'url' => $successUrl,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('PaddleBookingGateway: transaction failed', [
                    'booking_id' => $booking->id,
                    'body'       => $response->body(),
                ]);

                return null;
            }

            $transactionId = $response->json('data.id');
            $checkoutUrl = $response->json('data.checkout.url');

            if ($transactionId) {
                $booking->update(['checkout_session_id' => $transactionId]);
            }

            return $checkoutUrl;
        } catch (\Throwable $e) {
            Log::error('PaddleBookingGateway: exception', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function handleWebhook(array $payload): ?SlotReservation
    {
        $eventType = $payload['event_type'] ?? '';
        if ($eventType !== 'transaction.completed') {
            return null;
        }

        $data = $payload['data'] ?? [];
        $custom = $data['custom_data'] ?? [];
        $bookingId = $custom['booking_id'] ?? null;

        if (! $bookingId) {
            return null;
        }

        $booking = SlotReservation::withoutGlobalScope('tenant')->find($bookingId);
        if (! $booking) {
            return null;
        }

        app(BookingPaymentService::class)->markPaid(
            $booking,
            'paddle',
            $data['id'] ?? null,
            $data['id'] ?? null,
        );

        return $booking->fresh();
    }
}
