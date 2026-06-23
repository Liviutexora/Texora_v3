<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (!file_exists(base_path('.installed'))) {
            return $next($request);
        }
        $isMaintenance = cache('maintenance_mode', false);
        if ($isMaintenance && 
            !str_starts_with($request->path(), 'admin') && 
            !Str::contains($request->path(), 'livewire')) {
            $maintenanceMessage = Setting::get('maintenance_message');
            $maintenanceMessage = $maintenanceMessage ? $maintenanceMessage : 'We are currently under maintenance. Please check back later.';
            return response()->view('maintenance', ['maintenanceMessage' => $maintenanceMessage], 503);
        }
        return $next($request);
    }
}
