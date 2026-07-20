import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // Konfigurasi server hanya berlaku untuk pengembangan lokal, 
    // jadi tidak akan mengganggu deployment di Railway.
    server: {
        host: '127.0.0.1',
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // REVISI: Tambahkan blok build berikut agar Vite 
    // memastikan output hasil kompilasi masuk ke folder public/build
    build: {
        outDir: 'public/build',
        emptyOutDir: true, // Membersihkan folder build lama sebelum build baru
    },
});