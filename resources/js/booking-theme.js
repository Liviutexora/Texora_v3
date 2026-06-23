'use strict';

(function () {
    function applyBookingTheme() {
        var card = document.querySelector('.booking-card');
        if (!card) return;
        var dark = false;

        // Read tenant settings baked into the card element as data attributes
        // so we never need to re-inject PHP values after a Livewire morph.
        var forceDark    = card.dataset.forceDark    === '1';
        var matchSystem  = card.dataset.matchSystem  !== '0'; // default true

        if (forceDark) {
            dark = true;
        } else {
            // Explicit user preference in localStorage
            try { if (localStorage.getItem('theme') === 'dark') dark = true; } catch (e) {}
            // OS dark mode (when tenant allows it)
            if (matchSystem) {
                try { if (!dark && window.matchMedia('(prefers-color-scheme: dark)').matches) dark = true; } catch (e) {}
            }
            // Respect parent frame theme when embedded
            try {
                if (!dark && window.parent !== window) {
                    dark = window.parent.document.documentElement.classList.contains('dark');
                }
            } catch (e) {}
        }

        card.classList.toggle('bw-dark', dark);
        card.classList.toggle('bw-light', !dark);
    }

    // Apply on initial paint
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyBookingTheme);
    } else {
        applyBookingTheme();
    }

    // Re-apply after every Livewire morphdom update.
    if (!window.__bwThemeHooked) {
        window.__bwThemeHooked = true;
        var hookFn = function () {
            if (window.Livewire && window.Livewire.hook) {
                window.Livewire.hook('commit', function (args) {
                    args.succeed(function () { applyBookingTheme(); });
                });
            }
        };
        if (window.Livewire) {
            hookFn();
        } else {
            document.addEventListener('livewire:initialized', hookFn);
        }
    }
})();
