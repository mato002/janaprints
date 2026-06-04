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
                brand: {
                    navy: '#081229',
                    'navy-light': '#0F1D3D',
                    'navy-muted': '#1A2744',
                    magenta: '#FF2D75',
                    'magenta-hover': '#E02566',
                    orange: '#FF7A18',
                    'orange-hover': '#E56D15',
                    purple: '#6C4BFF',
                    'purple-hover': '#5A3FE0',
                    cyan: '#00D4FF',
                    'cyan-muted': '#00B8E0',
                    white: '#FFFFFF',
                    'off-white': '#FAFBFC',
                    'light-gray': '#F1F3F5',
                    'gray-muted': '#E8ECF0',
                    'text-primary': '#081229',
                    'text-secondary': '#4A5568',
                    'text-muted': '#718096',
                },
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
                display: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'display-xl': ['4.5rem', { lineHeight: '1.05', letterSpacing: '-0.02em', fontWeight: '800' }],
                'display-lg': ['3.75rem', { lineHeight: '1.08', letterSpacing: '-0.02em', fontWeight: '800' }],
                'display-md': ['3rem', { lineHeight: '1.1', letterSpacing: '-0.02em', fontWeight: '700' }],
                'display-sm': ['2.25rem', { lineHeight: '1.15', letterSpacing: '-0.01em', fontWeight: '700' }],
                'lead': ['1.25rem', { lineHeight: '1.7', fontWeight: '400' }],
                'body-lg': ['1.125rem', { lineHeight: '1.75', fontWeight: '400' }],
                'dashboard-title': ['1.75rem', { lineHeight: '2.25rem', fontWeight: '700' }],
                'section-title': ['1.125rem', { lineHeight: '1.5rem', fontWeight: '600' }],
                'card-title': ['0.8125rem', { lineHeight: '1.125rem', fontWeight: '500' }],
                'card-value': ['1.625rem', { lineHeight: '2rem', fontWeight: '700' }],
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(49 51 156 / 0.04), 0 1px 3px 0 rgb(15 23 42 / 0.04)',
                'card-hover': '0 2px 8px 0 rgb(49 51 156 / 0.08), 0 1px 3px 0 rgb(15 23 42 / 0.04)',
                'brand-sm': '0 2px 8px rgb(8 18 41 / 0.06)',
                'brand-md': '0 4px 20px rgb(8 18 41 / 0.08)',
                'brand-lg': '0 8px 40px rgb(8 18 41 / 0.12)',
                'brand-glow': '0 0 40px rgb(255 45 117 / 0.15)',
            },
            borderRadius: {
                'brand-sm': '0.5rem',
                'brand-md': '0.75rem',
                'brand-lg': '1rem',
                'brand-xl': '1.25rem',
                'brand-2xl': '1.5rem',
            },
            maxWidth: {
                'public-narrow': '42rem',
                'public-content': '72rem',
                'public-wide': '87.5rem',
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
