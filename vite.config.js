import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js','public/assets/src/css/tailwind.min.css',
                'public/frontend/assets/js/main.js',
                'public/frontend/assets/css/style.css',
                'public/assets/src/js/menus.js',
                'public/assets/src/js/tailwind.min.js',
                'public/assets/src/js/template.js',
                'public/assets/src/js/vendors.min.js',
                'public/assets/src/js/demo.js',
                'public/assets/src/js/jquery.smartmenus.js',
                'public/assets/src/css/horizontal-menu.css',
                'public/assets/src/css/custom.css',
                'public/assets/src/css/font-awesome-6.4.css',
                'public/assets/src/css/color_theme.css',
                'public/assets/src/css/skin_color.css',
                'public/assets/src/css/style_rtl.css',
                'public/assets/src/css/style.css',
                'public/assets/src/css/vendors_css.css',
            ],
            refresh: true,
        }),
    ],
    // css: {
    //     postcss: {
    //         plugins: [
    //             // PostCSS plugins
    //         ],
    //     },
    //     // Masukkan semua jalur CSS yang Anda butuhkan di sini
    //     preprocessorOptions: {
    //         // Misalnya:
    //         includePaths: [
    //             'public/assets/vendor_components/',
    //         ],
    //     },
    // },
});
