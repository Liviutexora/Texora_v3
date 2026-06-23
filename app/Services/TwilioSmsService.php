<?php

namespace App\Services;

use App\Support\TenantSmsSettings;
use Illuminate\Support\Facades\Http;

class TwilioSmsService
{
    public function send(int $tenantId, string $to, string $body): void
    {
        $settings = TenantSmsSettings::for($tenantId);

        if (! $settings->canSend()) {
            return;
        }

        $accountSid = $settings->accountSid();
        $authToken  = $settings->authToken();
        $from       = $settings->fromNumber();

        if (! $accountSid || ! $authToken || ! $from) {
            return;
        }

        Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'To'   => $to,
                'From' => $from,
                'Body' => $body,
            ])
            ->throw();
    }
}
