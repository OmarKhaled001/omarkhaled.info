import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/turnstile.js', 'resources/css/admin-fonts.css'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Fonts must stay separate files so they can be preloaded and cached long-term.
        assetsInlineLimit: 0,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
