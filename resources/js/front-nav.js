'use strict';

// Language dropdown toggle
(function () {
    var btn = document.getElementById('sl-lang-btn');
    var dd  = document.getElementById('sl-lang-dropdown');
    if (!btn || !dd) return;
    btn.addEventListener('click', function (e) { e.stopPropagation(); dd.classList.toggle('hidden'); });
    document.addEventListener('click', function () { dd.classList.add('hidden'); });
})();

// User dropdown toggle
(function () {
    var menu = document.getElementById('sl-user-menu');
    var btn  = document.getElementById('sl-user-btn');
    if (!menu || !btn) return;

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target)) {
            menu.classList.remove('open');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') menu.classList.remove('open');
    });
})();
