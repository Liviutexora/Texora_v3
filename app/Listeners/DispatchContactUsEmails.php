<?php

namespace App\Listeners;

use App\Events\ContactUsSubmitted;
use App\Jobs\SendContactUsEmails;

class DispatchContactUsEmails
{
    public function handle(object $event): void
    {
        if (! $event instanceof ContactUsSubmitted) {
            return;
        }

        SendContactUsEmails::dispatchSync($event->contactUs);
    }
}

