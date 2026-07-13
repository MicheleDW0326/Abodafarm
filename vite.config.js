import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
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
});
