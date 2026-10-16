import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Source Serif 4', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                brand: {
                    50: '#eef3ff',
                    100: '#d9e6ff',
                    200: '#b3ccff',
                    600: '#1d4fd7',
                    700: '#1a41ad',
                    800: '#16337f',
                    900: '#101f4b',
                    950: '#0a1430',
                },
                gold: {
                    100: '#faf3dd',
                    200: '#f3e5b5',
                    300: '#e9d189',
                    400: '#d9b64f',
                    500: '#c9a227',
                    600: '#a8861d',
                },
                cream: '#faf7f0',
            },
            boxShadow: {
                soft: '0 1px 2px rgb(28 25 23 / 0.05), 0 12px 32px -16px rgb(28 25 23 / 0.18)',
                lift: '0 2px 4px rgb(10 20 48 / 0.08), 0 16px 32px -16px rgb(22 51 127 / 0.35)',
                lux: '0 2px 6px rgb(10 20 48 / 0.12), 0 28px 56px -20px rgb(10 20 48 / 0.45)',
                glow: '0 0 0 1px rgb(201 162 39 / 0.25), 0 8px 32px -8px rgb(201 162 39 / 0.45)',
            },
        },
    },

    plugins: [forms],
};
