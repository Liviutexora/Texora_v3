'use strict';

function handleSlugSearch(e) {
    e.preventDefault();
    var slug = document.getElementById('tenant-slug').value.trim();
    var errEl = document.getElementById('slug-error');
    if (!slug) {
        errEl.classList.remove('hidden');
        return;
    }
    errEl.classList.add('hidden');
    window.location.href = '/' + encodeURIComponent(slug);
}

window.handleSlugSearch = handleSlugSearch;
