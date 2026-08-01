<?php

namespace App\Services\Pwa\Http\Controllers;

use App\Services\Pwa\Core\PwaServiceWorkerService;
use Illuminate\Http\Response;

class ServiceWorkerController
{
    public function __invoke(PwaServiceWorkerService $serviceWorkerService): Response
    {
        return response($serviceWorkerService->render(), 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}