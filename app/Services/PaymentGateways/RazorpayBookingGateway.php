<?php

namespace App\Services\PaymentGateways;

use App\Contracts\BookingPaymentGateway;
use App\Models\SlotReservation;
use App\Services\BookingPaymentService;
use App\Support\TenantPaymentSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayBookingGateway implements BookingPaymentGateway
{
    public function name(): string
    {
        return 'razorpay';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function createCheckoutUrl(SlotReservation $booking, string $successUrl, string $cancelUrl): ?string
    {
        $settings = TenantPaymentSettings::for($booking->tenant_id);

        if (! $settings->isGatewayConfigured('razorpay')) {
            return null;
        }

        $keyId = $settings->razorpayKeyId();
        $keySecret = $settings->razorpayKeySecret();

        $amountPaise = (int) round((float) $booking->amount * 100);
        $currency = strtoupper($booking->currency ?? 'INR');

        try {
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->post('https://api.razorpay.com/v1/payment_links', [
                    'amount'          => max($amountPaise, 100),
                    'currency'        => $currency,
                    'description'     => $booking->service?->name ?? __('Booking'),
                    'reference_id'    => (string) $booking->id,
                    'callback_url'    => $successUrl,
                    'callback_method' => 'get',
                    'notes'           => [
                        'booking_id' => $booking->id,
                        'tenant_id'  => $booking->tenant_id,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('RazorpayBookingGateway: payment link failed', [
                    'booking_id' => $booking->id,
                    'body'       => $response->body(),
                ]);

                return null;
            }

            $linkId = $response->json('id');
            $shortUrl = $response->json('short_url');

            if ($linkId) {
                $booking->update(['checkout_session_id' => $linkId]);
            }

            return $shortUrl;
        } catch (\Throwable $e) {
            Log::error('RazorpayBookingGateway: exception', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function handleWebhook(array $payload): ?SlotReservation
    {
        $event = $payload['event'] ?? '';
        if ($event !== 'payment_link.paid') {
            return null;
        }

        $entity = $payload['payload']['payment_link']['entity'] ?? [];
        $bookingId = $entity['reference_id'] ?? $entity['notes']['booking_id'] ?? null;

        if (! $bookingId) {
            return null;
        }

        $booking = SlotReservation::withoutGlobalScope('tenant')->find($bookingId);
        if (! $booking) {
            return null;
        }

        $paymentId = $payload['payload']['payment']['entity']['id'] ?? $entity['id'] ?? null;

        app(BookingPaymentService::class)->markPaid(
            $booking,
            'razorpay',
            $paymentId,
            $entity['id'] ?? null,
        );

        return $booking->fresh();
    }
}
