<?php

namespace App\Services;

use App\Filament\Pages\SmsTemplates;
use App\Models\Setting;
use App\Models\SlotReservation;

class BookingSmsMessageService
{
    public function confirmation(SlotReservation $booking): string
    {
        return $this->render(
            Setting::get('sms_template_confirmation', SmsTemplates::DEFAULTS['sms_template_confirmation']),
            $this->placeholders($booking)
        );
    }

    public function reminder(SlotReservation $booking): string
    {
        return $this->render(
            Setting::get('sms_template_reminder', SmsTemplates::DEFAULTS['sms_template_reminder']),
            $this->placeholders($booking)
        );
    }

    public function cancellation(SlotReservation $booking): string
    {
        return $this->render(
            Setting::get('sms_template_cancellation', SmsTemplates::DEFAULTS['sms_template_cancellation']),
            $this->placeholders($booking)
        );
    }

    public function rescheduled(SlotReservation $booking): string
    {
        return $this->render(
            Setting::get('sms_template_rescheduled', SmsTemplates::DEFAULTS['sms_template_rescheduled']),
            $this->placeholders($booking)
        );
    }

    private function render(string $template, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(SlotReservation $booking): array
    {
        $booking   = $booking->loadMissing(['tenant', 'service', 'providerRelation.user']);
        $cancelUrl = rescue(fn () => route('booking.cancel', $booking->cancellation_token), '');

        return [
            'CLIENT_NAME'   => $booking->name ?? '',
            'SERVICE_NAME'  => $booking->service?->name ?? '',
            'BOOKING_DATE'  => $booking->date?->format('D, d M Y') ?? '',
            'BOOKING_TIME'  => substr($booking->start_time ?? '', 0, 5),
            'BOOKING_ID'    => '#' . $booking->id,
            'PROVIDER_NAME' => $booking->providerRelation?->user?->name ?? '',
            'CANCEL_URL'    => $cancelUrl,
            'TENANT_NAME'   => $booking->tenant?->name ?? '',
        ];
    }
}
