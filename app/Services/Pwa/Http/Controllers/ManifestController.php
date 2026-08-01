<?php

namespace App\Services\Pwa\Http\Controllers;

use App\Services\Pwa\Contracts\PwaContextResolverInterface;
use App\Services\Pwa\Core\PwaManifestService;
use Illuminate\Http\JsonResponse;

class ManifestController
{
    public function __invoke(PwaContextResolverInterface $contextResolver, PwaManifestService $manifestService): JsonResponse
    {
        $context = $contextResolver->resolve(request());

        return response()->json(
            $manifestService->build($context),
            200,
            ['Content-Type' => 'application/manifest+json; charset=UTF-8'],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }
}