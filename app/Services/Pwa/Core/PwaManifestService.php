<?php

namespace App\Services\Pwa\Core;

use App\Services\Pwa\Contracts\PwaBrandingRepositoryInterface;
use App\Services\Pwa\DTO\PwaContextData;

class PwaManifestService
{
    public function __construct(
        private readonly PwaBrandingRepositoryInterface $brandingRepository,
        private readonly PwaIconFallbackResolver $iconFallbackResolver,
    )
    {
    }

    public function build(PwaContextData $context): array
    {
        $branding = $this->brandingRepository->getBranding($context);
        $iconUrls = [
            'any_192' => route('pwa.icon', array_merge(['purpose' => 'any', 'size' => 192], $context->manifestQuery())),
            'any_512' => route('pwa.icon', array_merge(['purpose' => 'any', 'size' => 512], $context->manifestQuery())),
            'maskable_512' => route('pwa.icon', array_merge(['purpose' => 'maskable', 'size' => 512], $context->manifestQuery())),
        ];

        return array_filter([
            'id' => $context->appId,
            'name' => $branding->appName,
            'short_name' => $branding->shortName,
            'start_url' => $context->startUrl,
            'scope' => $context->scope,
            'display' => config('pwa.manifest.display', 'standalone'),
            'display_override' => config('pwa.manifest.display_override', ['fullscreen', 'standalone', 'minimal-ui']),
            'orientation' => config('pwa.manifest.orientation', 'portrait'),
            'theme_color' => $branding->themeColor,
            'background_color' => $branding->backgroundColor,
            'icons' => $this->iconFallbackResolver->manifestIcons($iconUrls),
        ], static fn ($value) => $value !== null);
    }
}