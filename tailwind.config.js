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
                    primary: '#0F172A',
                    sidebar: '#111827',
                    accent: '#2563EB',
                    success: '#16A34A',
                    warning: '#F59E0B',
                    danger: '#DC2626',
                    info: '#0EA5E9',
                    page: '#F8FAFC',
                    card: '#FFFFFF',
                    border: '#E2E8F0',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'dashboard-title': ['2rem', { lineHeight: '2.5rem', fontWeight: '700' }],
                'section-title': ['1.25rem', { lineHeight: '1.75rem', fontWeight: '700' }],
                'card-title': ['0.875rem', { lineHeight: '1.25rem', fontWeight: '500' }],
                'card-value': ['2rem', { lineHeight: '2.5rem', fontWeight: '700' }],
            },
            boxShadow: {
                card: '0 1px 3px 0 rgb(15 23 42 / 0.06), 0 1px 2px -1px rgb(15 23 42 / 0.06)',
                'card-hover': '0 4px 6px -1px rgb(15 23 42 / 0.08), 0 2px 4px -2px rgb(15 23 42 / 0.06)',
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
        },
    },

    plugins: [forms],
};
