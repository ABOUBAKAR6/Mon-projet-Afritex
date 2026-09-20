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
            colors: {
                'afri-dark': '#4A1B0C',
                'afri-gold': '#C8963E',
                'afri-cream': '#F5E6D3',
                'afri-brown': '#8B4513',
                'afri-beige': '#FDF6EE',
                'afri-soft-gold': '#E3C58A',
            },
            fontFamily: {
                sans: ['Outfit', 'Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Fraunces', 'Playfair Display', ...defaultTheme.fontFamily.serif],
            },
        },
    },

    plugins: [forms],
};
