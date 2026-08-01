<?php

namespace App\Services\Pwa\Core;

use App\Services\Pwa\Contracts\PwaBrandingRepositoryInterface;
use App\Services\Pwa\DTO\PwaContextData;
use Illuminate\Support\Facades\File;

class PwaStaticArtifactPublisher
{
    public function __construct(
        private readonly PwaServiceWorkerService $serviceWorkerService,
        private readonly PwaVersionService $versionService,
        private readonly PwaBrandingRepositoryInterface $brandingRepository,
        private readonly PwaIconFallbackResolver $iconFallbackResolver,
    ) {
    }

    public function publish(PwaContextData $context): void
    {
        $baseDir = public_path('pwa');
        $iconsDir = $baseDir . DIRECTORY_SEPARATOR . 'icons';

        File::ensureDirectoryExists($iconsDir);

        $branding = $this->brandingRepository->getBranding($context);
        $label = $branding->shortName !== '' ? $branding->shortName : $branding->appName;

        $icons = [
            [
                'filename' => 'icon-192.png',
                'purpose' => 'any',
                'size' => 192,
                'maskable' => false,
                'manifest_purpose' => 'any',
            ],
            [
                'filename' => 'icon-512.png',
                'purpose' => 'any',
                'size' => 512,
                'maskable' => false,
                'manifest_purpose' => 'any',
            ],
            [
                'filename' => 'icon-maskable-512.png',
                'purpose' => 'maskable',
                'size' => 512,
                'maskable' => true,
                'manifest_purpose' => 'maskable',
            ],
        ];

        $manifestIcons = [];

        foreach ($icons as $icon) {
            $candidates = $this->iconFallbackResolver->sourceCandidates(
                $branding,
                $icon['purpose'],
                $icon['size'],
            );

            $png = $this->iconFallbackResolver->renderPng(
                $candidates,
                $icon['size'],
                $icon['maskable'],
                $label,
            );

            File::put($iconsDir . DIRECTORY_SEPARATOR . $icon['filename'], $png);

            $manifestIcons[] = [
                'src' => url('pwa/icons/' . $icon['filename']),
                'sizes' => $icon['size'] . 'x' . $icon['size'],
                'type' => 'image/png',
                'purpose' => $icon['manifest_purpose'],
            ];
        }

        $manifest = [
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
            'icons' => $manifestIcons,
        ];

        File::put(
            $baseDir . DIRECTORY_SEPARATOR . 'manifest.webmanifest',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );

        File::put(
            $baseDir . DIRECTORY_SEPARATOR . 'sw.js',
            $this->serviceWorkerService->render(),
        );

        File::put(
            $baseDir . DIRECTORY_SEPARATOR . 'version.json',
            json_encode($this->versionService->current()->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );
    }
}
