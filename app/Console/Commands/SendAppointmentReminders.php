<?php

namespace App\Console\Commands;

use App\Jobs\SendBookingReminderEmail;
use App\Jobs\SendBookingReminderSms;
use App\Models\SlotReservation;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature   = 'appointments:send-reminders {--date= : Override the target date (Y-m-d, defaults to tomorrow)}';
    protected $description = 'Send email and SMS reminders for appointments scheduled for tomorrow';

    public function handle(): int
    {
        $targetDate = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now()->addDay();

        $query = SlotReservation::query()
            ->where('status', 'confirmed')
            ->whereDate('date', $targetDate)
            ->with(['provider']);

        $count = $query->count();

        if ($count === 0) {
            $this->info("No confirmed appointments found for {$targetDate->toDateString()}.");
            return self::SUCCESS;
        }

        $this->info("Dispatching reminders for {$count} appointment(s) on {$targetDate->toDateString()}...");

        $query->chunk(100, function ($reservations) {
            foreach ($reservations as $reservation) {
                SendBookingReminderEmail::dispatch($reservation)->onQueue('notifications');
                SendBookingReminderSms::dispatch($reservation)->onQueue('notifications');
            }
        });

        $this->info('Done.');
        return self::SUCCESS;
    }
}
