/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './admin/templates/**/*.php',
        './admin/resources/js/**/*.js',
        './install/templates/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#eff6ff',
                    100: '#dbeafe',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
