'use strict';

(function () {
    // Wait for modal to be fully loaded
    function applyTailwindStyles() {
        var previewContent = document.querySelector('.preview-container .html-preview-content');
        if (!previewContent) return;

        // Decode any remaining HTML entities in the DOM
        var walker = document.createTreeWalker(
            previewContent,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        var node;
        while ((node = walker.nextNode())) {
            if (node.textContent.includes('&lt;') || node.textContent.includes('&gt;')) {
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = node.textContent;
                node.textContent = tempDiv.textContent || tempDiv.innerText || '';
            }
        }

        // Force style recalculation
        void previewContent.offsetHeight;
    }

    // Try multiple times to ensure modal is loaded
    setTimeout(applyTailwindStyles, 50);
    setTimeout(applyTailwindStyles, 200);
    setTimeout(applyTailwindStyles, 500);

    // Also listen for modal open events
    document.addEventListener('modal-opened', applyTailwindStyles);
})();
