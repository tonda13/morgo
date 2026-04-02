import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    publicDir: false,
    build: {
        outDir: 'public/admin/dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                admin: resolve(__dirname, 'admin/resources/js/admin.js'),
                style: resolve(__dirname, 'admin/resources/css/admin.css'),
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]',
            },
        },
    },
    css: {
        postcss: './postcss.config.js',
    },
});
