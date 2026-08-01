export class ServiceWorkerRegistrationService {
    constructor(store, options) {
        this.store = store;
        this.options = options;
    }

    async register() {
        if (!('serviceWorker' in navigator) || !this.options.serviceWorkerUrl) {
            return null;
        }

        try {
            const registration = await navigator.serviceWorker.register(this.options.serviceWorkerUrl, {
                scope: this.options.scope || '/',
            });

            registration.addEventListener('updatefound', () => {
                const worker = registration.installing;
                if (!worker) {
                    return;
                }

                worker.addEventListener('statechange', () => {
                    if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                        this.store.setState({ updateReady: true });
                        window.dispatchEvent(new CustomEvent('pwa:update-ready'));
                    }
                });
            });

            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data?.type === 'PWA_VERSION_READY') {
                    this.store.setState({ latestVersion: event.data.version });
                    window.dispatchEvent(new CustomEvent('pwa:version-ready', {
                        detail: event.data,
                    }));
                }
            });

            return registration;
        } catch (error) {
            console.warn('PWA service worker registration failed.', error);
            return null;
        }
    }
}