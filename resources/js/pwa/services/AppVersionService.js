export class AppVersionService {
    constructor(store, options) {
        this.store = store;
        this.options = options;
        this.intervalId = null;
    }

    async start() {
        await this.refreshVersion();

        if (!this.options.versionUrl || !this.options.checkIntervalMs) {
            return;
        }

        this.intervalId = window.setInterval(async () => {
            await this.refreshVersion();
        }, this.options.checkIntervalMs);
    }

    async refreshVersion() {
        if (!this.options.versionUrl) {
            return;
        }

        try {
            const response = await fetch(this.options.versionUrl, { cache: 'no-store' });
            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const state = this.store.getState();

            if (!state.currentVersion) {
                this.store.setState({
                    currentVersion: payload.version ?? null,
                    latestVersion: payload.version ?? null,
                });
                return;
            }

            if (payload.version && payload.version !== state.currentVersion) {
                this.store.setState({
                    latestVersion: payload.version,
                    updateReady: true,
                });
                window.dispatchEvent(new CustomEvent('pwa:update-ready', {
                    detail: payload,
                }));
            }
        } catch (error) {
            console.warn('PWA version check failed.', error);
        }
    }
}