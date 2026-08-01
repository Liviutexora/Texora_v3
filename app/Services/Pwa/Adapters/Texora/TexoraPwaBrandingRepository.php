<?php

namespace App\Services\Pwa\Adapters\Texora;

use App\Models\Setting;
use App\Services\Pwa\Contracts\PwaBrandingRepositoryInterface;
use App\Services\Pwa\DTO\PwaBrandingData;
use App\Services\Pwa\DTO\PwaContextData;

class TexoraPwaBrandingRepository implements PwaBrandingRepositoryInterface
{
    public function getBranding(PwaContextData $context): PwaBrandingData
    {
        $logoUrl = $this->storageUrl(Setting::get('site_logo'));
        $faviconUrl = $this->storageUrl(Setting::get('site_favicon')) ?: asset('favicon.ico');

        $icon192Url = $this->storageUrl(Setting::get('pwa_icon_192')) ?: $faviconUrl ?: $logoUrl;
        $icon512Url = $this->storageUrl(Setting::get('pwa_icon_512')) ?: $logoUrl ?: $faviconUrl;
        $maskableIcon512Url = $this->storageUrl(Setting::get('pwa_maskable_icon_512')) ?: $icon512Url ?: $logoUrl ?: $faviconUrl;

        $defaultName = (string) config('pwa.defaults.app_name', config('app.name'));

        return new PwaBrandingData(
            appName: (string) (Setting::get('pwa_app_name') ?: Setting::get('site_name', $defaultName) ?: $defaultName),
            shortName: (string) (Setting::get('pwa_short_name') ?: Setting::get('site_name', $defaultName) ?: $defaultName),
            themeColor: (string) (Setting::get('pwa_theme_color') ?: config('pwa.defaults.theme_color', '#111827')),
            backgroundColor: (string) (Setting::get('pwa_background_color') ?: config('pwa.defaults.background_color', '#ffffff')),
            logoUrl: $logoUrl,
            faviconUrl: $faviconUrl,
            icon192Url: $icon192Url,
            icon512Url: $icon512Url,
            maskableIcon512Url: $maskableIcon512Url,
        );
    }

    private function storageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}