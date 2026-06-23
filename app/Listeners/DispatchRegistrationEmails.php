<?php

namespace App\Listeners;

use App\Jobs\SendRegistrationEmails;
use Illuminate\Auth\Events\Registered;

class DispatchRegistrationEmails
{
    public function handle(object $event): void
    {
        if (! $event instanceof Registered) {
            return;
        }

        SendRegistrationEmails::dispatchSync($event->user);
    }
}

