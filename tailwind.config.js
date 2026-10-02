import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                aski: { 50: '#f1f8f3', 100: '#dcefe1', 500: '#198754', 600: '#147244', 700: '#0f5b36', 800: '#0c482c', 900: '#08331f' },
                gold: { 100: '#fff5cf', 400: '#e6b93f', 500: '#c99820', 600: '#a97914' },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
