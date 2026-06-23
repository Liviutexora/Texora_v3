'use strict';

(function () {
    var BLOCKED = ['save', 'create', 'saveAndCreateAnother', 'delete', 'forceDelete', 'restore'];

    // 1. Block Enter key inside text inputs from submitting the form
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        var tag = (e.target.tagName || '').toLowerCase();
        var type = (e.target.type || '').toLowerCase();
        if (tag === 'input' && type !== 'submit' && type !== 'button' && type !== 'checkbox' && type !== 'radio') {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    // 2. Intercept Livewire v3 commits that call blocked methods
    document.addEventListener('livewire:init', function () {
        Livewire.hook('commit', function (ref) {
            var commit = ref.commit;
            var calls  = (commit && commit.calls) ? commit.calls : [];
            var blocked = calls.some(function (c) { return BLOCKED.indexOf(c.method) !== -1; });
            if (!blocked) return;

            // Replace each blocked call's payload so nothing persists
            calls.forEach(function (c) {
                if (BLOCKED.indexOf(c.method) !== -1) {
                    c.method = '__demoBlocked__';
                }
            });
        });
    });
})();
