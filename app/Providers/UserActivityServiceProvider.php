<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Auth;
use App\Models\UserVisit;
use Illuminate\Support\Facades\DB;

class UserActivityServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Listen after request is handled, when session is available
        $this->app['events']->listen(RequestHandled::class, function (RequestHandled $event) {

            $request = $event->request;

            // Only track web requests (ignore console/api)
            if (!$request->isMethod('GET') || !$request->hasSession()) {
                return;
            }

            try {
                $pdo = DB::connection()->getPdo(); // Check DB connection
                if (!$pdo instanceof \PDO) {
                    return;
                }
                $sessionId = $request->session()->getId();
                $ip = $request->ip();
    
                // Check if this session is already logged today
                $alreadyVisited = UserVisit::where('session_id', $sessionId)
                    ->whereDate('created_at', now())
                    ->exists();
    
                if (!$alreadyVisited) {
                    UserVisit::create([
                        'ip_address' => $ip,
                        'user_id'    => Auth::id(),
                        'user_agent' => $request->userAgent(),
                        'session_id' => $sessionId,
                    ]);
                }
            } catch (\Exception $e) {
                return;
                // DB connection failed — keep defaults
            }
        });
    }
}
