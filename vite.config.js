import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
            // Optimize chunk splitting
            chunkingStrategy: {
                strategy: 'automatic',
                splitVendor: true,
            },
        }),
        tailwindcss(),
    ],

    // Build optimizations
    build: {
        // Target browsers for optimal output
        target: 'esnext',

        // Enable minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info'],
            },
            format: {
                comments: false,
            },
        },

        // CSS minification
        cssMinify: true,

        // Source maps for production debugging (disable for smaller files)
        sourcemap: false,

        // Rollup options for better chunking
        rollupOptions: {
            output: {
                // Asset file naming
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const ext = info[info.length - 1];
                    if (/\.(css)$/.test(assetInfo.name)) {
                        return 'assets/[name]-[hash][extname]';
                    }
                    if (/\.(woff2?|ttf|otf|eot)$/.test(assetInfo.name)) {
                        return 'assets/fonts/[name]-[hash][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
                // Chunk file naming
                chunkFileNames: 'assets/[name]-[hash].js',
                // Entry point naming
                entryFileNames: 'assets/[name]-[hash].js',
            },
        },

        // Enable CSS code splitting
        cssCodeSplit: true,

        // Asset inlining threshold (4kb)
        assetsInlineLimit: 4096,

        // Report compressed sizes
        reportCompressedSize: true,

        // Chunk size warning limit
        chunkSizeWarningLimit: 500,
    },

    // CSS configuration
    css: {
        devSourcemap: true,
    },

    // Server configuration for development
    server: {
        host: "abodafarm.test",
        hmr: {
            host: "abodafarm.test",
        },
        proxy: {
            "/livewire": {
                target: "http://localhost:8000",
                changeOrigin: true,
                secure: false,
            },
            "/api": {
                target: "http://localhost:8000",
                changeOrigin: true,
                secure: false,
            },
        },
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },

    // Preview configuration for production testing
    preview: {
        host: "abodafarm.test",
        port: 4173,
    },

    // Optimization configuration
    optimizeDeps: {
        // Pre-bundle these dependencies
        include: [
            'alpinejs',
            'tailwindcss',
        ],
        // Exclude from optimization
        exclude: [
            'livewire/livewire',
        ],
    },
});
