<?php

namespace App\Services\Pwa\Http\Controllers;

use App\Services\Pwa\Core\PwaVersionService;
use Illuminate\Http\JsonResponse;

class VersionController
{
    public function __invoke(PwaVersionService $versionService): JsonResponse
    {
        return response()->json($versionService->current()->toArray());
    }
}