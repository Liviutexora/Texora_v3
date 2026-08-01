<?php

namespace App\Services\Pwa\Core;

use App\Services\Pwa\Contracts\PwaBrandingRepositoryInterface;
use App\Services\Pwa\DTO\PwaContextData;

class PwaMetaTagService
{
    public function __construct(private readonly PwaBrandingRepositoryInterface $brandingRepository)
    {
    }

    public function build(PwaContextData $context): array
    {
        $branding = $this->brandingRepository->getBranding($context);

        return [
            'app_name' => $branding->appName,
            'theme_color' => $branding->themeColor,
            'background_color' => $branding->backgroundColor,
            'apple_touch_icon_url' => $branding->appleTouchIconUrl(),
            'apple_mobile_web_app_capable' => config('pwa.apple.capable', true) ? 'yes' : 'no',
            'apple_mobile_web_app_status_bar_style' => config('pwa.apple.status_bar_style', 'default'),
        ];
    }
}