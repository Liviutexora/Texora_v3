{{-- Styles --}}
<script src="{{ theme_asset('js/tailwindcss.js') }}"></script>
<link rel="stylesheet" href="{{ theme_asset('css/googleapis-css2.css') }}">
<script>
    'use strict';
    tailwind.config = {
        darkMode: 'class', // Use class-based dark mode instead of media query
        theme: {
            extend: {
                colors: {
                    primary: {
                        DEFAULT: '#FF2D00', // main red (fallback, overridden by CSS)
                        dark: '#2C3E50', // dark blue
                        light: '#ECF0F1', // light gray
                    },
                    secondary: {
                        DEFAULT: '#6C757D', // grey
                        dark: '#495057',
                        light: '#E9ECEF',
                    },
                    info: {
                        DEFAULT: '#17A2B8', // blueish
                        dark: '#117A8B',
                        light: '#D1ECF1',
                    },
                    warning: {
                        DEFAULT: '#FFC107', // yellow
                        dark: '#FFB300',
                        light: '#FFF3CD',
                    },
                    success: {
                        DEFAULT: '#28A745', // green
                        dark: '#1E7E34',
                        light: '#D4EDDA',
                    },
                    danger: {
                        DEFAULT: '#DC3545', // red
                        dark: '#C82333',
                        light: '#F8D7DA',
                    },
                },
            },
        },
    }
</script>
<style>
    :root {
        --primary: 249, 115, 22;
        --secondary: 22, 163, 74;
    }

    [data-color="orange"] {
        --primary: 249, 115, 22;
        --secondary: 22, 163, 74;
    }

    [data-color="blue"] {
        --primary: 59, 130, 246;
        --secondary: 16, 185, 129;
    }

    [data-color="purple"] {
        --primary: 168, 85, 247;
        --secondary: 34, 197, 94;
    }

    [data-color="rose"] {
        --primary: 244, 63, 94;
        --secondary: 52, 211, 153;
    }

    [data-color="teal"] {
        --primary: 20, 184, 166;
        --secondary: 251, 191, 36;
    }

    body {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    body.dark-mode {
        background-color: #1a1a1a;
        color: #f5f5f5;
    }

    body.dark-mode .bg-white {
        background-color: #2a2a2a !important;
    }

    body.dark-mode .bg-gray-50 {
        background-color: #1f1f1f !important;
    }

    body.dark-mode .bg-gray-100 {
        background-color: #2a2a2a !important;
    }

    body.dark-mode .text-gray-900 {
        color: #f5f5f5 !important;
    }

    body.dark-mode .text-gray-800 {
        color: #e5e5e5 !important;
    }

    body.dark-mode .text-gray-700 {
        color: #d4d4d4 !important;
    }

    body.dark-mode .text-gray-600 {
        color: #a3a3a3 !important;
    }

    body.dark-mode .border-gray-200 {
        border-color: #404040 !important;
    }

    body.dark-mode input,
    body.dark-mode textarea,
    body.dark-mode select {
        background-color: #2a2a2a !important;
        color: #f5f5f5 !important;
        border-color: #404040 !important;
    }

    /* Override Tailwind primary colors to use CSS variables */
    .text-primary {
        color: rgb(var(--primary)) !important;
    }

    .hover\:text-primary:hover {
        color: rgb(var(--primary)) !important;
    }

    /* Dark mode primary colors - brighter for better visibility */
    body.dark-mode .text-primary {
        color: rgb(var(--primary)) !important;
        filter: brightness(1.3);
    }

    body.dark-mode .hover\:text-primary:hover {
        color: rgb(var(--primary)) !important;
        filter: brightness(1.5);
    }

    /* Dark mode hover states for links */
    body.dark-mode a.hover\:text-primary:hover,
    body.dark-mode button.hover\:text-primary:hover {
        color: rgb(var(--primary)) !important;
        filter: brightness(1.5);
    }

    .bg-primary {
        background-color: rgb(var(--primary)) !important;
    }

    .border-primary {
        border-color: rgb(var(--primary)) !important;
    }

    .primary-bg {
        background-color: rgb(var(--primary));
    }

    .primary-color {
        color: rgb(var(--primary));
    }

    .secondary-bg {
        background-color: rgb(var(--secondary));
    }

    .secondary-color {
        color: rgb(var(--secondary));
    }
</style>
<link rel="stylesheet" href="{{ theme_asset('css/all.min.css') }}">