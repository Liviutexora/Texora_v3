<?php

namespace App\Jobs;

use App\Models\NotificationPreference;
use App\Models\SlotReservation;
use App\Services\BookingSmsMessageService;
use App\Services\TwilioSmsService;
use App\Support\TenantSmsSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingReminderSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly SlotReservation $booking) {}

    public function handle(BookingSmsMessageService $messages, TwilioSmsService $sms): void
    {
        if (! $this->booking->phone) {
            return;
        }

        if ($this->booking->sms_reminder_sent_at) {
            return;
        }

        $settings = TenantSmsSettings::for($this->booking->tenant_id);

        if (! $settings->canSend() || ! $settings->isReminderEnabled()) {
            return;
        }

        if (! NotificationPreference::isSmsEnabled('booking_reminder')) {
            return;
        }

        $sms->send(
            $this->booking->tenant_id,
            $this->booking->phone,
            $messages->reminder($this->booking),
        );

        $this->booking->updateQuietly(['sms_reminder_sent_at' => now()]);
    }
}
