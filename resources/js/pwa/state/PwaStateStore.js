export class PwaStateStore {
    constructor(initialState = {}) {
        this.state = {
            canInstall: false,
            isInstalled: false,
            isIos: false,
            isStandalone: false,
            deferredPrompt: null,
            updateReady: false,
            currentVersion: null,
            latestVersion: null,
            ...initialState,
        };
        this.listeners = new Set();
    }

    getState() {
        return { ...this.state };
    }

    setState(patch) {
        this.state = { ...this.state, ...patch };
        for (const listener of this.listeners) {
            listener(this.getState());
        }
    }

    subscribe(listener) {
        this.listeners.add(listener);
        listener(this.getState());

        return () => {
            this.listeners.delete(listener);
        };
    }
}