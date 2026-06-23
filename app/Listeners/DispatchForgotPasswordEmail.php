<?php

namespace App\Listeners;

use App\Events\ForgotPasswordRequested;
use App\Jobs\SendForgotPasswordEmail;

class DispatchForgotPasswordEmail
{
    public function handle(object $event): void
    {
        if (! $event instanceof ForgotPasswordRequested) {
            return;
        }

        SendForgotPasswordEmail::dispatchSync($event->user, $event->resetLink);
    }
}

