/**
 * Slotara embed.js — drop-in script for inline embeds and popup triggers.
 * Works cross-origin via postMessage for auto-resizing.
 */
(function (global) {
    'use strict';

    var BASE = (function () {
        var s = document.currentScript;
        if (s && s.src) {
            var u = new URL(s.src);
            return u.origin;
        }
        return '';
    })();

    // ── 1. Auto-init [data-slotara] divs ────────────────────────────────
    function initInlineEmbeds() {
        document.querySelectorAll('[data-slotara]').forEach(function (el) {
            if (el.dataset.slotaraInit) return;
            el.dataset.slotaraInit = '1';

            var slug = el.getAttribute('data-slotara');
            var iframe = document.createElement('iframe');
            iframe.src = BASE + '/' + slug + '?embed=inline';
            iframe.setAttribute('style', 'border:0;width:100%;min-height:680px;display:block;');
            iframe.setAttribute('loading', 'lazy');
            iframe.setAttribute('title', 'Book an appointment');
            el.appendChild(iframe);

            // Auto-resize via postMessage
            window.addEventListener('message', function (e) {
                if (e.source !== iframe.contentWindow) return;
                if (e.data && e.data.type === 'slotara:resize' && typeof e.data.height === 'number') {
                    iframe.style.height = Math.max(400, e.data.height) + 'px';
                }
            });
        });
    }

    // ── 2. Popup helper ───────────────────────────────────────────────────
    function popup(slug) {
        // Prevent duplicate overlays
        if (document.getElementById('slotara-overlay')) return;

        var overlay = document.createElement('div');
        overlay.id = 'slotara-overlay';
        overlay.setAttribute('style', [
            'position:fixed;inset:0;z-index:2147483647',
            'background:rgba(0,0,0,.55)',
            'display:flex;align-items:center;justify-content:center',
            'padding:16px',
            'animation:slotara-fade-in .2s ease',
        ].join(';'));

        var modal = document.createElement('div');
        modal.setAttribute('style', [
            'position:relative',
            'width:100%;max-width:680px',
            'max-height:92vh',
            'border-radius:18px',
            'overflow:hidden',
            'box-shadow:0 24px 64px rgba(0,0,0,.35)',
            'background:#fff',
            'animation:slotara-slide-up .25s cubic-bezier(.4,0,.2,1)',
        ].join(';'));

        // Close button
        var closeBtn = document.createElement('button');
        closeBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
        closeBtn.setAttribute('aria-label', 'Close');
        closeBtn.setAttribute('style', [
            'position:absolute;top:12px;right:12px;z-index:1',
            'width:30px;height:30px',
            'border:none;border-radius:50%',
            'background:rgba(0,0,0,.12)',
            'color:#fff;cursor:pointer',
            'display:flex;align-items:center;justify-content:center',
            'transition:background .15s',
        ].join(';'));
        closeBtn.onmouseover = function () { this.style.background = 'rgba(0,0,0,.22)'; };
        closeBtn.onmouseout  = function () { this.style.background = 'rgba(0,0,0,.12)'; };

        var iframe = document.createElement('iframe');
        iframe.src = BASE + '/' + slug + '?embed=popup';
        iframe.setAttribute('style', 'border:0;width:100%;height:600px;display:block;transition:height .15s ease;');
        iframe.setAttribute('title', 'Book an appointment');

        function close() {
            document.body.removeChild(overlay);
            document.removeEventListener('keydown', onKey);
        }

        function onKey(e) { if (e.key === 'Escape') close(); }

        closeBtn.onclick = close;
        overlay.onclick  = function (e) { if (e.target === overlay) close(); };
        document.addEventListener('keydown', onKey);

        // Resize iframe (and therefore modal) to match content height
        window.addEventListener('message', function (e) {
            if (e.source !== iframe.contentWindow) return;
            if (e.data && e.data.type === 'slotara:resize' && typeof e.data.height === 'number') {
                var maxH = Math.floor(window.innerHeight * 0.92);
                var h = Math.min(maxH, Math.max(400, e.data.height));
                iframe.style.height = h + 'px';
            }
        });

        modal.appendChild(closeBtn);
        modal.appendChild(iframe);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
    }

    // ── 3. postMessage height reporter (runs inside embedded iframes) ─────
    function initHeightReporter() {
        if (window.parent === window) return; // not embedded
        function report() {
            var h = document.documentElement.scrollHeight || document.body.scrollHeight;
            window.parent.postMessage({ type: 'slotara:resize', height: h }, '*');
        }
        // Report on load and on Livewire updates
        report();
        var ro = new ResizeObserver(report);
        ro.observe(document.body);
        document.addEventListener('livewire:load', report);
        document.addEventListener('livewire:update', report);
    }

    // ── 4. CSS animations (injected once) ────────────────────────────────
    function injectStyles() {
        if (document.getElementById('slotara-embed-styles')) return;
        var style = document.createElement('style');
        style.id = 'slotara-embed-styles';
        style.textContent = [
            '@keyframes slotara-fade-in{from{opacity:0}to{opacity:1}}',
            '@keyframes slotara-slide-up{from{transform:translateY(24px);opacity:0}to{transform:translateY(0);opacity:1}}',
        ].join('');
        document.head.appendChild(style);
    }

    // ── Boot ──────────────────────────────────────────────────────────────
    injectStyles();

    if (window.parent !== window) {
        // We're inside an iframe — report heights to parent
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHeightReporter);
        } else {
            initHeightReporter();
        }
    } else {
        // We're on the host page — init inline embeds
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initInlineEmbeds);
        } else {
            initInlineEmbeds();
        }
    }

    // Expose public API
    global.Slotara = { popup: popup };

})(window);
