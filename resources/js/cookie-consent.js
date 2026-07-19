'use strict';

(function () {
    var banner = document.getElementById('tx-cookie-consent');
    var acceptBtn = document.getElementById('tx-cookie-consent-accept');

    if (!banner || !acceptBtn) return;

    var COOKIE_NAME = 'texora_cookie_consent';
    var COOKIE_TTL_DAYS = 365;

    // Keep payload structure future-ready for consent categories.
    function buildConsentPayload() {
        return {
            version: 1,
            categories: {
                necessary: true,
                analytics: false,
                marketing: false,
            },
            acceptedAt: Date.now(),
        };
    }

    function getCookieValue(name) {
        var parts = document.cookie ? document.cookie.split('; ') : [];

        for (var i = 0; i < parts.length; i += 1) {
            var segment = parts[i].split('=');
            var key = segment.shift();
            if (key === name) {
                return segment.join('=');
            }
        }

        return null;
    }

    function hasValidConsent() {
        var raw = getCookieValue(COOKIE_NAME);
        if (!raw) return false;

        try {
            var parsed = JSON.parse(decodeURIComponent(raw));
            return Boolean(parsed && parsed.categories && parsed.categories.necessary === true);
        } catch (error) {
            return false;
        }
    }

    function setConsentCookie(payload) {
        var expiresAt = new Date();
        expiresAt.setDate(expiresAt.getDate() + COOKIE_TTL_DAYS);

        var cookie =
            COOKIE_NAME + '=' + encodeURIComponent(JSON.stringify(payload)) +
            '; expires=' + expiresAt.toUTCString() +
            '; path=/' +
            '; SameSite=Lax';

        if (window.location.protocol === 'https:') {
            cookie += '; Secure';
        }

        document.cookie = cookie;
    }

    function showBanner() {
        banner.classList.remove('hidden');
        banner.setAttribute('aria-hidden', 'false');
    }

    function hideBanner() {
        banner.classList.add('hidden');
        banner.setAttribute('aria-hidden', 'true');
    }

    if (!hasValidConsent()) {
        showBanner();
    }

    acceptBtn.addEventListener('click', function () {
        setConsentCookie(buildConsentPayload());
        hideBanner();
    });
})();