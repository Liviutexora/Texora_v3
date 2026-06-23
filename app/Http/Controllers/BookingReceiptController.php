<?php

namespace App\Http\Controllers;

use App\Models\SlotReservation;
use Illuminate\View\View;

class BookingReceiptController extends Controller
{
    public function show(string $token): View
    {
        $booking = SlotReservation::withoutGlobalScope('tenant')
            ->with(['service', 'providerRelation.user', 'tenant'])
            ->where('cancellation_token', $token)
            ->firstOrFail();

        return view('booking.receipt', [
            'booking' => $booking,
        ]);
    }
}
