'use strict';

function slotaraDemoFill(email) {
    // Works with Livewire wire:model bindings — uses native setter + bubbling events
    var emailEl = document.querySelector('input[wire\\:model*="email"], input[name*="email"], input[type="email"]');
    var passEl  = document.querySelector('input[wire\\:model*="password"], input[name*="password"], input[type="password"]');

    var setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;

    if (emailEl) {
        setter.call(emailEl, email);
        emailEl.dispatchEvent(new Event('input',  { bubbles: true }));
        emailEl.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (passEl) {
        setter.call(passEl, 'password');
        passEl.dispatchEvent(new Event('input',  { bubbles: true }));
        passEl.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Scroll sign-in button into view
    var submit = document.querySelector('[type="submit"]');
    if (submit) { submit.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
}

window.slotaraDemoFill = slotaraDemoFill;
