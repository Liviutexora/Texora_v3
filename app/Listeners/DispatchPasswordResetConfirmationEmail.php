<?php

namespace App\Listeners;

use App\Jobs\SendPasswordResetConfirmationEmail;
use Illuminate\Auth\Events\PasswordReset;

class DispatchPasswordResetConfirmationEmail
{
    public function handle(object $event): void
    {
        if (! $event instanceof PasswordReset) {
            return;
        }

        SendPasswordResetConfirmationEmail::dispatchSync($event->user);
    }
}

