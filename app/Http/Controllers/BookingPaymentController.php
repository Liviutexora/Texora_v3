<?php

namespace App\Http\Controllers;

use App\Models\SlotReservation;
use App\Services\BookingPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingPaymentController extends Controller
{
    public function success(Request $request, string $token, BookingPaymentService $payments): RedirectResponse|View
    {
        $booking = $this->findBooking($token);
        if (! $booking) {
            abort(404);
        }

        $sessionId = $request->query('session_id')
            ?? $request->query('razorpay_payment_link_id')
            ?? $request->query('token');

        if ($sessionId) {
            $payments->completeFromReturnUrl($booking, (string) $sessionId);
            $booking->refresh();
        }

        return view('booking.payment-success', [
            'booking' => $booking,
            'receiptUrl' => route('booking.receipt', ['token' => $booking->cancellation_token]),
        ]);
    }

    public function cancel(string $token): View
    {
        $booking = $this->findBooking($token);
        if (! $booking) {
            abort(404);
        }

        return view('booking.payment-cancel', ['booking' => $booking]);
    }

    private function findBooking(string $token): ?SlotReservation
    {
        return SlotReservation::withoutGlobalScope('tenant')
            ->where('cancellation_token', $token)
            ->first();
    }
}
