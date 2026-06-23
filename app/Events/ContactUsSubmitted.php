<?php

namespace App\Events;

use App\Models\ContactUs;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactUsSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ContactUs $contactUs)
    {
        //
    }
}

