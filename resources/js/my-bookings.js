'use strict';

(function () {
    var i18n = window.slotlineI18n || {};
    var t = {
        yesCancelBooking:   i18n.yesCancelBooking   || 'Yes, cancel booking',
        cancelling:         i18n.cancelling         || 'Cancelling…',
        cancelled:          i18n.cancelled          || 'Cancelled',
        couldNotCancel:     i18n.couldNotCancel     || 'Could not cancel. Please try again.',
        networkError:       i18n.networkError       || 'Network error. Please try again.',
    };
    // ── Past bookings toggle ───────────────────────────────────
    var pastToggle  = document.getElementById('sl-past-toggle');
    var pastSection = document.getElementById('sl-past-section');
    if (pastToggle && pastSection) {
        pastToggle.addEventListener('click', function () {
            var open = pastSection.classList.toggle('open');
            pastToggle.classList.toggle('sl-past-open', open);
        });
    }

    // ── Cancel modal ──────────────────────────────────────────
    var overlay  = document.getElementById('sl-cancel-overlay');
    var modal    = document.getElementById('sl-cancel-modal');
    var reason   = document.getElementById('sl-cancel-reason');
    var errEl    = document.getElementById('sl-cancel-error');
    var subtitle = document.getElementById('sl-cancel-subtitle');
    var btnConfirm  = document.getElementById('sl-cancel-confirm');
    var btnDismiss  = document.getElementById('sl-cancel-dismiss');
    var btnClose    = document.getElementById('sl-cancel-close');
    var activeToken = null;
    var activeCard  = null;

    // Open modal from "Cancel" button on any booking card
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-cancel-token]');
        if (!btn) return;
        e.preventDefault();
        activeToken = btn.dataset.cancelToken;
        activeCard  = btn.closest('[data-booking-id]');
        subtitle.textContent = btn.dataset.cancelLabel || '';
        reason.value = '';
        errEl.textContent = ''; errEl.classList.add('hidden');
        btnConfirm.disabled = false;
        btnConfirm.textContent = t.yesCancelBooking;
        overlay.classList.add('open');
        reason.focus();
    });

    function closeModal() {
        overlay.classList.remove('open');
        activeToken = null;
        activeCard  = null;
    }

    if (btnDismiss) btnDismiss.addEventListener('click', closeModal);
    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (overlay) overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });

    // Submit cancellation
    if (btnConfirm) btnConfirm.addEventListener('click', function () {
        if (!activeToken) return;
        btnConfirm.disabled = true;
        btnConfirm.textContent = t.cancelling;
        errEl.classList.add('hidden');

        fetch('/my-bookings/cancel/' + activeToken, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reason: reason.value.trim() }),
        })
        .then(function (res) { return res.json().then(function (d) { return { ok: res.ok, data: d }; }); })
        .then(function (r) {
            if (r.ok && r.data.success) {
                // Update the card in-place: grey it out and swap status badge
                if (activeCard) {
                    var badge = activeCard.querySelector('[data-status-badge]');
                    if (badge) {
                        badge.textContent = t.cancelled;
                        badge.className = 'flex-shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500';
                    }
                    var cancelBtn = activeCard.querySelector('[data-cancel-token]');
                    if (cancelBtn) cancelBtn.remove();
                    activeCard.classList.add('opacity-60');
                }
                closeModal();
            } else {
                errEl.textContent = r.data.error || t.couldNotCancel;
                errEl.classList.remove('hidden');
                btnConfirm.disabled = false;
                btnConfirm.textContent = t.yesCancelBooking;
            }
        })
        .catch(function () {
            errEl.textContent = t.networkError;
            errEl.classList.remove('hidden');
            btnConfirm.disabled = false;
            btnConfirm.textContent = t.yesCancelBooking;
        });
    });
})();
