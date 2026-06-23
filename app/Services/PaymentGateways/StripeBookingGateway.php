<?php

namespace App\Services\PaymentGateways;

use App\Contracts\BookingPaymentGateway;
use App\Models\SlotReservation;
use App\Services\BookingPaymentService;
use App\Support\TenantPaymentSettings;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class StripeBookingGateway implements BookingPaymentGateway
{
    public function name(): string
    {
        return 'stripe';
    }

    public function isConfigured(): bool
    {
        return class_exists(Stripe::class);
    }

    public function createCheckoutUrl(SlotReservation $booking, string $successUrl, string $cancelUrl): ?string
    {
        $settings = TenantPaymentSettings::for($booking->tenant_id);

        if (! $settings->isGatewayConfigured('stripe')) {
            return null;
        }

        try {
            Stripe::setApiKey($settings->stripeSecretKey());

            $amountCents = (int) round((float) $booking->amount * 100);
            $currency = strtolower($booking->currency ?? 'usd');

            $session = CheckoutSession::create([
                'mode'                 => 'payment',
                'payment_method_types' => ['card'],
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => $currency,
                        'unit_amount'  => max($amountCents, 50),
                        'product_data' => [
                            'name' => $booking->service?->name ?? __('Booking'),
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => $successUrl . (str_contains($successUrl, '?') ? '&' : '?') . 'session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => $cancelUrl,
                'client_reference_id' => (string) $booking->id,
                'metadata' => [
                    'booking_id' => $booking->id,
                    'tenant_id'  => $booking->tenant_id,
                    'type'       => 'booking_payment',
                ],
            ]);

            $booking->update(['checkout_session_id' => $session->id]);

            return $session->url;
        } catch (\Throwable $e) {
            Log::error('StripeBookingGateway: checkout failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function handleWebhook(array $payload): ?SlotReservation
    {
        $type = $payload['type'] ?? '';
        if ($type !== 'checkout.session.completed') {
            return null;
        }

        $session = $payload['data']['object'] ?? [];
        if (($session['mode'] ?? '') !== 'payment') {
            return null;
        }

        if (($session['metadata']['type'] ?? '') !== 'booking_payment') {
            return null;
        }

        $bookingId = $session['metadata']['booking_id'] ?? $session['client_reference_id'] ?? null;
        if (! $bookingId) {
            return null;
        }

        $booking = SlotReservation::withoutGlobalScope('tenant')->find($bookingId);
        if (! $booking) {
            return null;
        }

        app(BookingPaymentService::class)->markPaid(
            $booking,
            'stripe',
            $session['payment_intent'] ?? $session['id'] ?? null,
            $session['id'] ?? null,
        );

        return $booking->fresh();
    }
}
