import { PwaStateStore } from './state/PwaStateStore';
import { InstallationDetectionService } from './services/InstallationDetectionService';
import { AppInstallService } from './services/AppInstallService';
import { ServiceWorkerRegistrationService } from './services/ServiceWorkerRegistrationService';
import { AppVersionService } from './services/AppVersionService';

const options = window.__PWA__;

if (!options?.enabled) {
    // no-op
} else {
    const store = new PwaStateStore();
    const detection = new InstallationDetectionService(store);
    const installer = new AppInstallService(store, {
        startUrl: options.startUrl,
        iosInstructions: options.iosInstructions,
        unsupportedMessage: options.unsupportedMessage,
        iosLabel: options.iosLabel,
        installLabel: options.installLabel,
        openLabel: options.openLabel,
    });
    const serviceWorker = new ServiceWorkerRegistrationService(store, {
        serviceWorkerUrl: options.serviceWorkerUrl,
        scope: options.scope,
    });
    const version = new AppVersionService(store, {
        versionUrl: options.versionUrl,
        checkIntervalMs: options.checkIntervalMs,
    });

    const boot = async () => {
        detection.start();
        installer.mountButtons();
        await serviceWorker.register();
        await version.start();
        window.PwaInstaller = {
            install: () => installer.install(),
            state: () => store.getState(),
        };
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
}