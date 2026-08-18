import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/recipes/create.js',
                'resources/js/week-menu/index.js',
                'resources/css/week-menu.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
