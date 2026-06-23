<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDocsWhenDisabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) Setting::get('docs_enabled', true)) {
            abort(404);
        }

        return $next($request);
    }
}
