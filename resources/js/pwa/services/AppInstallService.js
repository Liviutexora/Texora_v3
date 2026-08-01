export class AppInstallService {
    constructor(store, options) {
        this.store = store;
        this.options = options;
        this.buttons = new Set();
        this.hints = new Set();
    }

    mountButtons() {
        document.querySelectorAll('[data-pwa-install-button]').forEach((button) => {
            if (this.buttons.has(button)) {
                return;
            }

            this.buttons.add(button);
            button.addEventListener('click', async () => {
                await this.install();
            });
        });

        document.querySelectorAll('[data-pwa-install-hint]').forEach((hint) => {
            this.hints.add(hint);
        });

        this.store.subscribe((state) => {
            const mode = this.resolveMode(state);
            const label = this.resolveLabel(mode);
            const hint = this.resolveHint(mode);

            for (const button of this.buttons) {
                const shouldShow = mode === 'install' || mode === 'ios' || mode === 'unsupported' || mode === 'installed';
                button.hidden = !shouldShow;

                if (button.dataset.pwaInstallPreserveContent === '1') {
                    continue;
                }

                button.textContent = label;
            }

            for (const hintNode of this.hints) {
                hintNode.hidden = hint === '';
                hintNode.textContent = hint;
            }
        });
    }

    async install() {
        const state = this.store.getState();

        if (state.isInstalled) {
            window.location.assign(this.options.startUrl || '/');
            return;
        }

        if (state.isIos && !state.isInstalled) {
            window.alert(this.options.iosInstructions);
            return;
        }

        if (state.isUnsupported) {
            window.alert(this.options.unsupportedMessage);
            return;
        }

        if (!state.deferredPrompt) {
            return;
        }

        state.deferredPrompt.prompt();
        const choice = await state.deferredPrompt.userChoice;

        this.store.setState({
            deferredPrompt: null,
            canInstall: false,
        });

        window.dispatchEvent(new CustomEvent('pwa:install-choice', {
            detail: choice,
        }));
    }

    resolveMode(state) {
        if (state.isInstalled) {
            return 'installed';
        }

        if (state.isIos) {
            return 'ios';
        }

        if (state.canInstall && state.deferredPrompt) {
            return 'install';
        }

        if (state.isUnsupported) {
            return 'unsupported';
        }

        return 'pending';
    }

    resolveLabel(mode) {
        if (mode === 'installed') {
            return this.options.openLabel;
        }

        if (mode === 'ios') {
            return this.options.iosLabel;
        }

        return this.options.installLabel;
    }

    resolveHint(mode) {
        if (mode === 'ios') {
            return this.options.iosInstructions;
        }

        if (mode === 'unsupported') {
            return this.options.unsupportedMessage;
        }

        return '';
    }
}