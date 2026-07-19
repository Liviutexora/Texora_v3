<div
    id="tx-cookie-consent"
    class="fixed inset-x-0 bottom-4 z-50 hidden px-4 sm:px-6"
    role="dialog"
    aria-live="polite"
    aria-label="Cookie consent"
>
    <div class="mx-auto w-full max-w-3xl rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-sm backdrop-blur sm:p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-sm font-bold text-gray-900">Cookie-uri</h2>
                <p class="mt-1 text-sm leading-relaxed text-gray-600">
                    Texora utilizeaza cookie-uri necesare pentru functionarea aplicatiei si pentru imbunatatirea experientei de utilizare.
                </p>
            </div>

            <div class="flex items-center gap-2 sm:flex-shrink-0">
                <button
                    id="tx-cookie-consent-accept"
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-violet-700"
                >
                    Accept
                </button>
                <a
                    href="{{ route('page.show', 'politica-cookie-urilor') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50"
                >
                    Mai multe informatii
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var banner = document.getElementById('tx-cookie-consent');
        var acceptButton = document.getElementById('tx-cookie-consent-accept');
        var cookieName = 'texora_cookie_consent';

        if (!banner || !acceptButton) {
            return;
        }

        function getCookie(name) {
            var parts = document.cookie ? document.cookie.split('; ') : [];

            for (var i = 0; i < parts.length; i += 1) {
                var row = parts[i].split('=');
                var key = row.shift();

                if (key === name) {
                    return row.join('=');
                }
            }

            return null;
        }

        function setConsentCookie() {
            var expires = new Date();
            expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));

            var value = encodeURIComponent('accepted');
            var cookie = cookieName + '=' + value + '; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';

            if (window.location.protocol === 'https:') {
                cookie += '; Secure';
            }

            document.cookie = cookie;
        }

        function showBanner() {
            banner.classList.remove('hidden');
        }

        function hideBanner() {
            banner.classList.add('hidden');
        }

        if (getCookie(cookieName)) {
            hideBanner();
        } else {
            showBanner();
        }

        acceptButton.addEventListener('click', function () {
            setConsentCookie();
            hideBanner();
        });
    })();
</script>