<?php

namespace App\Services;

use App\Contracts\BookingPaymentGateway;
use App\Jobs\DeleteBookingFromGoogleCalendar;
use App\Jobs\SyncBookingToGoogleCalendar;
use App\Models\SlotReservation;
use App\Services\PaymentGateways\PaddleBookingGateway;
use App\Services\PaymentGateways\PayPalBookingGateway;
use App\Services\PaymentGateways\RazorpayBookingGateway;
use App\Services\PaymentGateways\StripeBookingGateway;
use App\Support\TenantCalendarSettings;
use App\Support\TenantPaymentSettings;
use Illuminate\Support\Facades\Log;

class BookingPaymentService
{
    public function __construct(
        private readonly BookingNotificationService $notifications,
    ) {}

    public function requiresPaymentAtBooking(SlotReservation $booking): bool
    {
        $settings = TenantPaymentSettings::for($booking->tenant_id);

        if (! $settings->isEnabled() || ! $settings->requirePaymentAtBooking()) {
            return false;
        }

        return (float) ($booking->amount ?? 0) > 0;
    }

    public function gateway(?string $name = null, ?int $tenantId = null): ?BookingPaymentGateway
    {
        if ($name && $tenantId && ! TenantPaymentSettings::for($tenantId)->isGatewayConfigured($name)) {
            return null;
        }

        $gateway = match ($name) {
            'stripe'   => app(StripeBookingGateway::class),
            'razorpay' => app(RazorpayBookingGateway::class),
            'paypal'   => app(PayPalBookingGateway::class),
            'paddle'   => app(PaddleBookingGateway::class),
            default    => null,
        };

        if ($gateway && $gateway->isConfigured()) {
            return $gateway;
        }

        return null;
    }

    public function activeGateway(int $tenantId): ?BookingPaymentGateway
    {
        $settings = TenantPaymentSettings::for($tenantId);
        $name = $settings->activeGateway();

        return $name ? $this->gateway($name, $tenantId) : null;
    }

    public function createCheckoutUrl(SlotReservation $booking, string $successUrl, string $cancelUrl): ?string
    {
        $gateway = $this->activeGateway($booking->tenant_id);

        if (! $gateway) {
            Log::warning('BookingPaymentService: no configured payment gateway', [
                'tenant_id'  => $booking->tenant_id,
                'booking_id' => $booking->id,
            ]);

            return null;
        }

        $url = $gateway->createCheckoutUrl($booking, $successUrl, $cancelUrl);

        if ($url) {
            $booking->update([
                'payment_gateway' => $gateway->name(),
            ]);
        }

        return $url;
    }

    public function markPaid(
        SlotReservation $booking,
        string $gateway,
        ?string $reference = null,
        ?string $checkoutSessionId = null,
    ): void {
        $wasPending = $booking->payment_status === 'pending';

        $booking->update([
            'payment_status'        => 'paid',
            'payment_gateway'       => $gateway,
            'payment_reference'     => $reference ?? $booking->payment_reference,
            'paid_at'               => now(),
            'checkout_session_id'   => $checkoutSessionId ?? $booking->checkout_session_id,
        ]);

        if ($wasPending) {
            $this->notifications->dispatchForNewBooking($booking->fresh());
        }

        $this->maybeSyncCalendar($booking->fresh());
    }

    public function recordOfflinePayment(SlotReservation $booking, string $method, ?string $reference = null): void
    {
        $settings = TenantPaymentSettings::for($booking->tenant_id);

        $allowed = match ($method) {
            'cash'          => $settings->offlineCashEnabled(),
            'card_terminal' => $settings->offlineCardEnabled(),
            'bank_transfer' => $settings->offlineBankTransferEnabled(),
            'manual'        => true,
            default         => false,
        };

        if (! $allowed) {
            throw new \InvalidArgumentException("Offline payment method [{$method}] is not enabled.");
        }

        $this->markPaid($booking, $method, $reference);
    }

    public function completeFromReturnUrl(SlotReservation $booking, ?string $sessionId = null): bool
    {
        if ($booking->payment_status === 'paid') {
            return true;
        }

        // Return URL completion is a fallback when webhooks are delayed.
        $this->markPaid($booking, $booking->payment_gateway ?? 'stripe', $sessionId, $sessionId);

        return true;
    }

    public function maybeSyncCalendar(SlotReservation $booking): void
    {
        $calendar = TenantCalendarSettings::for($booking->tenant_id);

        if (! $calendar->isEnabled()) {
            return;
        }

        SyncBookingToGoogleCalendar::dispatch($booking)->afterCommit();
    }

    public function maybeDeleteCalendar(SlotReservation $booking): void
    {
        $calendar = TenantCalendarSettings::for($booking->tenant_id);

        if (! $calendar->isEnabled()) {
            return;
        }

        if (! $booking->google_calendar_event_id) {
            return;
        }

        DeleteBookingFromGoogleCalendar::dispatch($booking)->afterCommit();
    }
}
