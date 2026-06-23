<?php

namespace App\Contracts;

use App\Models\SlotReservation;

interface BookingPaymentGateway
{
    public function name(): string;

    public function isConfigured(): bool;

    /**
     * Create a checkout session and return the redirect URL, or null on failure.
     */
    public function createCheckoutUrl(SlotReservation $booking, string $successUrl, string $cancelUrl): ?string;

    /**
     * Mark booking paid from a verified webhook/callback payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(array $payload): ?SlotReservation;
}
