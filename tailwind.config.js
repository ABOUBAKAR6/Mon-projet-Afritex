import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/*
  Système visuel AfriTex (structure du projet Église, couleurs AfriTex)
  Les couleurs sont des variables CSS définies dans resources/css/app.css :
  les vues utilisent des rôles (papier, encre, brun, or…), jamais des hex.
*/

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        // Angles droits partout : seules les pastilles rondes (points, avatars) gardent rounded-full
        borderRadius: {
            none: '0',
            sm: '0',
            DEFAULT: '0',
            md: '0',
            lg: '0',
            xl: '0',
            '2xl': '0',
            '3xl': '0',
            full: '9999px',
        },
        extend: {
            colors: {
                papier: 'var(--papier)',
                'papier-2': 'var(--papier-2)',
                carte: 'var(--carte)',
                encre: 'var(--encre)',
                'encre-2': 'var(--encre-2)',
                'encre-3': 'var(--encre-3)',
                trait: 'var(--trait)',
                'trait-fort': 'var(--trait-fort)',
                brun: 'var(--brun)',
                'brun-fonce': 'var(--brun-fonce)',
                'brun-doux': 'var(--brun-doux)',
                'brun-trait': 'var(--brun-trait)',
                or: 'var(--or)',
                'or-clair': 'var(--or-clair)',
                'or-doux': 'var(--or-doux)',
                succes: 'var(--succes)',
                'succes-doux': 'var(--succes-doux)',
                alerte: 'var(--alerte)',
                'alerte-doux': 'var(--alerte-doux)',
                danger: 'var(--danger)',
                'danger-doux': 'var(--danger-doux)',

                // Anciens noms, gardés pour les fragments non refaits : ils pointent vers les rôles
                'afri-dark': 'var(--brun-fonce)',
                'afri-brown': 'var(--brun)',
                'afri-gold': 'var(--or)',
                'afri-cream': 'var(--papier-2)',
                'afri-beige': 'var(--papier)',
                'afri-soft-gold': 'var(--or-clair)',
            },
            fontFamily: {
                sans: ['Geist', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', 'Geist', ...defaultTheme.fontFamily.sans],
                serif: ['Poppins', 'Geist', ...defaultTheme.fontFamily.sans],
            },
            transitionTimingFunction: {
                'out-fort': 'cubic-bezier(0.23, 1, 0.32, 1)',
            },
            zIndex: {
                dropdown: '20',
                sticky: '30',
                overlay: '40',
                modal: '50',
                toast: '60',
            },
            minHeight: {
                11: '2.75rem',
                13: '3.25rem',
            },
            height: {
                13: '3.25rem',
            },
        },
    },

    plugins: [forms],
};
