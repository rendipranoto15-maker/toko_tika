import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Menegaskan lokasi build agar sinkron dengan Laravel
            buildDirectory: 'build',
        }),
    ],
    build: {
        // Railway butuh direktori output yang spesifik
        outDir: 'public/build',
        emptyOutDir: true,
        // Manifest wajib ada agar Laravel bisa menemukan file hasil compile
        manifest: true,
    },
});