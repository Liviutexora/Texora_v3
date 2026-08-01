export class InstallationDetectionService {
    constructor(store) {
        this.store = store;
        this.mediaQuery = window.matchMedia('(display-mode: standalone), (display-mode: fullscreen), (display-mode: minimal-ui)');
    }

    start() {
        const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
        const standalone = window.navigator.standalone === true || this.mediaQuery.matches;
        const supportsPromptEvent = 'onbeforeinstallprompt' in window || 'BeforeInstallPromptEvent' in window;

        this.store.setState({
            isIos,
            isStandalone: standalone,
            isInstalled: standalone,
            supportsPrompt: supportsPromptEvent,
            isUnsupported: !isIos && !supportsPromptEvent,
        });

        this.mediaQuery.addEventListener('change', (event) => {
            this.store.setState({
                isStandalone: event.matches,
                isInstalled: event.matches,
            });
        });

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();

            this.store.setState({
                deferredPrompt: event,
                supportsPrompt: true,
                isUnsupported: false,
                canInstall: !this.store.getState().isInstalled,
            });

            window.dispatchEvent(new CustomEvent('pwa:install-available'));
        });

        window.addEventListener('appinstalled', () => {
            this.store.setState({
                deferredPrompt: null,
                canInstall: false,
                isInstalled: true,
                isStandalone: true,
            });

            window.dispatchEvent(new CustomEvent('pwa:installed'));
        });

        if (isIos && !standalone) {
            this.store.setState({
                canInstall: true,
                isUnsupported: false,
            });
        }
    }
}