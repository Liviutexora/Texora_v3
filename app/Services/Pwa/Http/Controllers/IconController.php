<?php

namespace App\Services\Pwa\Http\Controllers;

use App\Services\Pwa\Contracts\PwaBrandingRepositoryInterface;
use App\Services\Pwa\Contracts\PwaContextResolverInterface;
use App\Services\Pwa\Core\PwaIconFallbackResolver;
use Illuminate\Http\Response;

class IconController
{
    public function __invoke(
        string $purpose,
        int $size,
        PwaContextResolverInterface $contextResolver,
        PwaBrandingRepositoryInterface $brandingRepository,
        PwaIconFallbackResolver $iconFallbackResolver,
    ): Response {
        if (! in_array($purpose, ['any', 'maskable'], true) || ! in_array($size, [192, 512], true)) {
            abort(404);
        }

        $context = $contextResolver->resolve(request())->with(
            request()->query('app_id'),
            request()->query('start_url'),
            request()->query('scope'),
        );

        $branding = $brandingRepository->getBranding($context);
        $candidates = $iconFallbackResolver->sourceCandidates($branding, $purpose, $size);
        $label = $branding->shortName !== '' ? $branding->shortName : $branding->appName;
        $png = $iconFallbackResolver->renderPng($candidates, $size, $purpose === 'maskable', $label);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
