import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        outDir: 'admin/dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                admin: resolve(__dirname, 'admin/resources/js/admin.js'),
            },
        },
    },
    css: {
        postcss: './postcss.config.js',
    },
});
