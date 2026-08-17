import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/form-group.js',
                'resources/sass/bootstrap.scss',
                'resources/js/bootstrap.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Silences deprecation warnings from node_modules (Bootstrap)
                quietDeps: true,
                
                // Optional: If you want to explicitly hide the @import warning 
                // until Bootstrap releases a native @use version
                silenceDeprecations: ['import', 'global-builtin'],
            },
        },
    },
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
