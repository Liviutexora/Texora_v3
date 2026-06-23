<?php 

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPasswordExpiry
{
    public function handle(Request $request, Closure $next)
    {
        if (! file_exists(base_path('.installed'))) {
            return $next($request);
        }

        if ($request->user() && $request->user()->isPasswordExpired()) {
            return redirect()->route('password.request')
                ->with('status', __('Your password has expired. Please reset it to continue.'));
        }

        return $next($request);
    }
}
