import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/public.css',
                'resources/css/login.css',
                'resources/js/app.js',
                'resources/js/public.js',
                'resources/js/login.js',
            ],
            refresh: true,
        }),
    ],
});
