import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/tenant-panel.css',
                'resources/css/fb-classes.css',
                'resources/js/app.js',
'resources/js/front-nav.js',
                'resources/js/welcome.js',
                'resources/js/demo-mode-banner.js',
                'resources/js/html-preview.js',
                'resources/js/login-demo-panel.js',
                'resources/js/my-bookings.js',
                'resources/js/booking-theme.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
