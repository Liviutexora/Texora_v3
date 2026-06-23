<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use App\Events\ContactUsSubmitted;
use App\Events\ForgotPasswordRequested;
use App\Listeners\DispatchContactUsEmails;
use App\Listeners\DispatchForgotPasswordEmail;
use App\Listeners\DispatchPasswordResetConfirmationEmail;
use App\Listeners\DispatchRegistrationEmails;
use App\Listeners\LogFilamentLoginActivity;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class FilamentEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogFilamentLoginActivity::class,
        ],
        Logout::class => [
            LogFilamentLoginActivity::class,
        ],
        Registered::class => [
            DispatchRegistrationEmails::class,
        ],
        ContactUsSubmitted::class => [
            DispatchContactUsEmails::class,
        ],
        ForgotPasswordRequested::class => [
            DispatchForgotPasswordEmail::class,
        ],
        PasswordReset::class => [
            DispatchPasswordResetConfirmationEmail::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Disable auto-discovery so listeners are only registered once
     * via the $listen array above. Without this, Laravel discovers
     * listeners a second time from the Listeners/ directory, causing
     * every event to dispatch its handler twice.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
