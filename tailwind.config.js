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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // CSS-variable-backed so utilities like bg-accent-500/20 keep working
                // while responding to the light/dark theme swap (nocturne-tokens.css).
                bg: 'rgb(var(--color-bg-rgb) / <alpha-value>)',
                surface: 'rgb(var(--color-surface-rgb) / <alpha-value>)',
                ink: 'rgb(var(--color-text-rgb) / <alpha-value>)',
                accent: {
                    DEFAULT: 'rgb(var(--color-accent-rgb) / <alpha-value>)',
                    100: 'rgb(var(--color-accent-100-rgb) / <alpha-value>)',
                    200: 'rgb(var(--color-accent-200-rgb) / <alpha-value>)',
                    300: 'rgb(var(--color-accent-300-rgb) / <alpha-value>)',
                    400: 'rgb(var(--color-accent-400-rgb) / <alpha-value>)',
                    500: 'rgb(var(--color-accent-500-rgb) / <alpha-value>)',
                    600: 'rgb(var(--color-accent-600-rgb) / <alpha-value>)',
                    700: 'rgb(var(--color-accent-700-rgb) / <alpha-value>)',
                    800: 'rgb(var(--color-accent-800-rgb) / <alpha-value>)',
                    900: 'rgb(var(--color-accent-900-rgb) / <alpha-value>)',
                },
                neutral: {
                    100: 'rgb(var(--color-neutral-100-rgb) / <alpha-value>)',
                    200: 'rgb(var(--color-neutral-200-rgb) / <alpha-value>)',
                    300: 'rgb(var(--color-neutral-300-rgb) / <alpha-value>)',
                    400: 'rgb(var(--color-neutral-400-rgb) / <alpha-value>)',
                    500: 'rgb(var(--color-neutral-500-rgb) / <alpha-value>)',
                    600: 'rgb(var(--color-neutral-600-rgb) / <alpha-value>)',
                    700: 'rgb(var(--color-neutral-700-rgb) / <alpha-value>)',
                    800: 'rgb(var(--color-neutral-800-rgb) / <alpha-value>)',
                    900: 'rgb(var(--color-neutral-900-rgb) / <alpha-value>)',
                },
                section: {
                    DEFAULT: 'rgb(var(--color-section-rgb) / <alpha-value>)',
                    glow: 'rgb(var(--color-section-glow-rgb) / <alpha-value>)',
                    ghost: 'rgb(var(--color-section-ghost-rgb) / <alpha-value>)',
                },
            },
            borderRadius: { sm: '4px', md: '8px', lg: '14px' },
            boxShadow: {
                sm: 'var(--shadow-sm)',
                md: 'var(--shadow-md)',
                lg: 'var(--shadow-lg)',
            },
        },
    },

    plugins: [forms],
};
