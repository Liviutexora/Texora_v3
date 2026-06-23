<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\SlotReservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendWebhookPayload implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly SlotReservation $booking) {}

    public function backoff(): array
    {
        return [10, 60, 300]; // seconds between retry attempts
    }

    public function handle(): void
    {
        $tenantId = $this->booking->tenant_id;
        $url      = (string) Setting::get("tenant_{$tenantId}_webhook_url", '');

        if ($url === '') {
            return;
        }

        $secret = (string) Setting::get("tenant_{$tenantId}_webhook_secret", '');

        $payload = json_encode([
            'event'      => 'booking.confirmed',
            'booking_id' => $this->booking->id,
            'tenant_id'  => $tenantId,
            'service'    => $this->booking->service?->name,
            'provider'   => $this->booking->provider?->user?->name,
            'date'       => $this->booking->date,
            'start_time' => $this->booking->start_time,
            'end_time'   => $this->booking->end_time,
            'name'       => $this->booking->name,
            'email'      => $this->booking->email,
            'phone'      => $this->booking->phone,
            'status'     => $this->booking->status,
            'amount'     => $this->booking->amount,
            'currency'   => $this->booking->currency,
            'timestamp'  => now()->toIso8601String(),
        ]);

        $signature = hash_hmac('sha256', $payload, $secret);

        Http::timeout(10)
            ->withHeaders(['X-Slotara-Signature' => 'sha256=' . $signature])
            ->withBody($payload, 'application/json')
            ->post($url)
            ->throw();
    }
}
