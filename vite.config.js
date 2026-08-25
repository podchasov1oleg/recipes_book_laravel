import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/ts/app.ts',
                'resources/ts/recipes/create.ts',
                'resources/ts/week-menu/index.ts',
                'resources/css/week-menu.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
