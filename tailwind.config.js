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
                // The single shared font across both the public site and
                // the authenticated dashboard — both layouts apply it via
                // their own explicit `font-sans` class on <body>.
                sans: ['Syne', ...defaultTheme.fontFamily.sans],
                pacifico: ['Pacifico', 'cursive'],
            },
            // Every rounded-* utility (buttons, inputs, cards, badges, pills,
            // avatars) is scaled to 10% of Tailwind's stock radius — a subtle
            // softening rather than the fully square edges used previously.
            // "full" stays functionally circular (999px still reads as a full
            // pill/circle at UI sizes) rather than scaling down with the rest.
            borderRadius: {
                none: '0px',
                sm: '0.0125rem',
                DEFAULT: '0.025rem',
                md: '0.0375rem',
                lg: '0.05rem',
                xl: '0.075rem',
                '2xl': '0.1rem',
                '3xl': '0.15rem',
                full: '999px',
            },
        },
    },

    plugins: [forms],
};
