<?php

namespace App\Jobs;

use App\Models\SlotReservation;
use App\Services\GoogleCalendarSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteBookingFromGoogleCalendar implements ShouldQueue
{
    use Queueable;

    public function __construct(public SlotReservation $booking) {}

    public function handle(GoogleCalendarSyncService $calendar): void
    {
        $calendar->deleteBookingFromCalendar($this->booking);
    }
}
