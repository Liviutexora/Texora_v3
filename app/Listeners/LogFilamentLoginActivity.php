<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\LoginActivity;
use Illuminate\Support\Facades\Log;

class LogFilamentLoginActivity
{
    public function handle(object $event): void
    {
        $user = $event->user;
        $guard = $event->guard ?? null;

        // Log::info('Login and Logout event fired!', [
        //     'guard' => $guard,
        //     'filament_guard' => filament()->getAuthGuard(),
        //     'user_id' => $user->id ?? null,
        //     'event' => get_class($event),
        // ]);

        if ($guard !== filament()->getAuthGuard()) {
            return;
        }

        if ($event instanceof Login) {
            LoginActivity::create([
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_in_at' => now(),
            ]);
        }

        if ($event instanceof Logout) {
            LoginActivity::where('user_id', $user->id)
                ->latest('id')
                ->first()?->update([
                    'logged_out_at' => now(),
                ]);
        }
    }

}
