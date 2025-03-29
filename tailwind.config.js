import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors'; // importa as cores do tailwind

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/css/**/*.css',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Cores customizadas
                primary: '#004b6b',
                secondary: '#f8ecda',

                // Sobrescrevendo algumas se quiser
                indigo: {
                    ...colors.indigo,
                    500: '#004b6b',
                    600: '#00384e',
                },

                // Ou redefinindo tons de cinza
                gray: colors.zinc,
            },
        },
    },

    plugins: [forms],
};
