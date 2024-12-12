import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/webcam.js',
                'resources/js/test-monitoring.js' 

            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    webcam: ['resources/js/webcam.js'],
                    monitoring: ['resources/js/test-monitoring.js']
                }
            }
        },
        sourcemap: false,
        minify: false 
    }
});
