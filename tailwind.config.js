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
                erp: {
                    /** Structure — navigation, headers, hierarchy */
                    primary: '#31339C',
                    secondary: '#5A5CC8',
                    sidebar: '#1F237A',
                    /** Action — buttons, active selections, links */
                    accent: '#D41484',
                    'accent-hover': '#B61070',
                    'accent-light': '#E856A6',
                    /** Semantic — unchanged */
                    success: '#16A34A',
                    warning: '#F59E0B',
                    danger: '#DC2626',
                    info: '#5A5CC8',
                    /** Surfaces */
                    page: '#F8F9FC',
                    card: '#FFFFFF',
                    border: '#E5E7EB',
                    readonly: '#F5F6FA',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'dashboard-title': ['1.75rem', { lineHeight: '2.25rem', fontWeight: '700' }],
                'section-title': ['1.125rem', { lineHeight: '1.5rem', fontWeight: '600' }],
                'card-title': ['0.8125rem', { lineHeight: '1.125rem', fontWeight: '500' }],
                'card-value': ['1.625rem', { lineHeight: '2rem', fontWeight: '700' }],
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(49 51 156 / 0.04), 0 1px 3px 0 rgb(15 23 42 / 0.04)',
                'card-hover': '0 2px 8px 0 rgb(49 51 156 / 0.08), 0 1px 3px 0 rgb(15 23 42 / 0.04)',
            },
            spacing: {
                sidebar: '260px',
                'sidebar-collapsed': '72px',
            },
            width: {
                sidebar: '260px',
                'sidebar-collapsed': '72px',
            },
            transitionDuration: {
                sidebar: '200ms',
            },
            borderWidth: {
                3: '3px',
            },
        },
    },

    plugins: [forms],
};
