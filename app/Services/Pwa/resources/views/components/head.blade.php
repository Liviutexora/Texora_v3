<link rel="manifest" href="{{ $manifestUrl }}">
<meta name="application-name" content="{{ $meta['app_name'] }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="{{ $meta['apple_mobile_web_app_capable'] }}">
<meta name="apple-mobile-web-app-title" content="{{ $meta['app_name'] }}">
<meta name="apple-mobile-web-app-status-bar-style" content="{{ $meta['apple_mobile_web_app_status_bar_style'] }}">
<meta name="theme-color" content="{{ $meta['theme_color'] }}">
@if ($meta['apple_touch_icon_url'])
<link rel="apple-touch-icon" href="{{ $meta['apple_touch_icon_url'] }}">
@endif

<script>
window.__PWA__ = {
    enabled: true,
    appId: @js($context->appId),
    startUrl: @js($context->startUrl),
    scope: @js($context->scope),
    manifestUrl: @js($manifestUrl),
    serviceWorkerUrl: @js($serviceWorkerUrl),
    versionUrl: @js($versionUrl),
    checkIntervalMs: @js($checkIntervalMs),
    iosInstructions: @js($iosInstructions),
    unsupportedMessage: @js((string) config('pwa.install.unsupported_message', 'Installation is not available in this browser.')),
    iosLabel: @js((string) config('pwa.install.ios_label', 'How to install on iOS')),
    installLabel: @js((string) config('pwa.install.label', 'Install app')),
    openLabel: @js((string) config('pwa.install.open_label', 'Open app')),
};
</script>

@vite($viteEntry)