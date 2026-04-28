import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/js"),
        },
    },
    test: {
        globals: true,
        environment: "jsdom",
        include: ["resources/js/**/*.{spec,test}.{js,ts}"],
        setupFiles: ["resources/js/test/setup.js"],
    },
});
