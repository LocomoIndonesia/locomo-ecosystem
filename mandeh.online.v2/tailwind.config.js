import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Modules/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                "on-primary-fixed": "#001b3c",
                "on-primary": "#ffffff",
                "primary-fixed-dim": "#a7c8ff",
                "inverse-on-surface": "#f1f1ef",
                "on-tertiary-fixed": "#00006e",
                "outline-variant": "#c3c6d1",
                "surface-container-high": "#e8e8e6",
                "on-secondary": "#ffffff",
                "surface-container": "#eeeeec",
                "surface-bright": "#f9f9f7",
                "on-error": "#ffffff",
                "secondary-fixed-dim": "#59de9b",
                "surface-container-low": "#f4f4f2",
                "surface-container-lowest": "#ffffff",
                "on-primary-container": "#799dd6",
                "on-tertiary": "#ffffff",
                "inverse-primary": "#a7c8ff",
                "surface-container-highest": "#e2e3e1",
                "secondary-container": "#75f8b3",
                "on-tertiary-fixed-variant": "#0102ee",
                "surface": "#f9f9f7",
                "error-container": "#ffdad6",
                "primary-container": "#003366",
                "surface-dim": "#dadad8",
                "on-secondary-fixed": "#002111",
                "outline": "#737780",
                "on-secondary-container": "#007147",
                "on-surface": "#1a1c1b",
                "background": "#f9f9f7",
                "error": "#ba1a1a",
                "on-surface-variant": "#43474f",
                "tertiary-container": "#0000b5",
                "tertiary-fixed-dim": "#bfc2ff",
                "secondary-fixed": "#78fbb6",
                "on-background": "#1a1c1b",
                "primary": "#001e40",
                "tertiary-fixed": "#e0e0ff",
                "secondary": "#006d43",
                "inverse-surface": "#2f3130",
                "surface-tint": "#3a5f94",
                "on-error-container": "#93000a",
                "tertiary": "#000076",
                "on-primary-fixed-variant": "#1f477b",
                "surface-variant": "#e2e3e1",
                "primary-fixed": "#d5e3ff",
                "on-tertiary-container": "#8a92ff",
                "on-secondary-fixed-variant": "#005232"
            },
            borderRadius: {
                "DEFAULT": "0.125rem",
                "lg": "0.25rem",
                "xl": "0.5rem",
                "full": "0.75rem"
            },
            spacing: {
                "lg": "48px",
                "gutter": "24px",
                "base": "4px",
                "md": "24px",
                "xs": "8px",
                "xl": "80px",
                "sm": "16px",
                "margin": "32px"
            },
            boxShadow: {
                'premium': '0 4px 24px -8px rgba(0, 51, 102, 0.08)',
                'premium-hover': '0 8px 32px -8px rgba(0, 51, 102, 0.12)',
                'modal': '0 8px 30px rgb(0, 30, 64, 0.08)',
            },
            fontFamily: {
                sans: ['Work Sans', ...defaultTheme.fontFamily.sans],
                'headline-lg': ['Inter', ...defaultTheme.fontFamily.sans],
                'body-md': ['Work Sans', ...defaultTheme.fontFamily.sans],
                'display-xl': ['Inter', ...defaultTheme.fontFamily.sans],
                'body-lg': ['Work Sans', ...defaultTheme.fontFamily.sans],
                'label-md': ['Inter', ...defaultTheme.fontFamily.sans],
                'headline-md': ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                "headline-lg": ["32px", { lineHeight: "40px", letterSpacing: "-0.01em", fontWeight: "700" }],
                "body-md": ["16px", { lineHeight: "24px", fontWeight: "500" }],
                "display-xl": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "800" }],
                "body-lg": ["18px", { lineHeight: "28px", fontWeight: "500" }],
                "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.05em", fontWeight: "600" }],
                "headline-md": ["24px", { lineHeight: "32px", fontWeight: "700" }]
            },
            transitionProperty: {
                'height': 'height',
                'spacing': 'margin, padding',
            }
        },
    },

    plugins: [forms, require('@tailwindcss/container-queries')],
};
