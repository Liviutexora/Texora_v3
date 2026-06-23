<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserVisit;
use Illuminate\Support\Facades\DB;

class TrackUserVisit
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $pdo = DB::connection()->getPdo(); // Check DB connection
            if ($pdo instanceof \PDO) {
                if ($request->hasSession()) {
                    $sessionId = $request->session()->getId();
                    $ip = $request->ip();
        
                    $alreadyVisited = UserVisit::where('session_id', $sessionId)
                        ->whereDate('created_at', now()->toDateString())
                        ->exists();
        
                    if (!$alreadyVisited) {
                        UserVisit::create([
                            'ip_address' => $ip,
                            'user_id' => Auth::id(),
                            'user_agent' => $request->userAgent(),
                            'session_id' => $sessionId,
                        ]);
                    }
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $next($request);
    }
}
