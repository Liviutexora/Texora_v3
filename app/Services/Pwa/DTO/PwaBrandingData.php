<?php

namespace App\Services\Pwa\DTO;

final class PwaBrandingData
{
    public function __construct(
        public readonly string $appName,
        public readonly string $shortName,
        public readonly string $themeColor,
        public readonly string $backgroundColor,
        public readonly ?string $logoUrl = null,
        public readonly ?string $faviconUrl = null,
        public readonly ?string $icon192Url = null,
        public readonly ?string $icon512Url = null,
        public readonly ?string $maskableIcon512Url = null,
    ) {
    }

    public function appleTouchIconUrl(): ?string
    {
        return $this->icon192Url
            ?? $this->icon512Url
            ?? $this->maskableIcon512Url
            ?? $this->faviconUrl
            ?? $this->logoUrl;
    }

    public function manifestIcons(): array
    {
        $icons = [];

        if ($this->icon192Url) {
            $icons[] = [
                'src' => $this->icon192Url,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ];
        }

        if ($this->icon512Url) {
            $icons[] = [
                'src' => $this->icon512Url,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ];
        }

        if ($this->maskableIcon512Url) {
            $icons[] = [
                'src' => $this->maskableIcon512Url,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ];
        }

        return $icons;
    }
}