import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],
    theme: {
        extend: {
            colors: {
                "outline-variant": "#4d4732",
                "faction-nisnas": "#50C878",
                "primary-fixed": "#ffe16d",
                "on-primary": "#3a3000",
                "surface-container-lowest": "#0e0e0e",
                "tertiary-container": "#bdddff",
                "primary-fixed-dim": "#e9c400",
                "on-error": "#690005",
                "glass-stroke": "rgba(255, 255, 255, 0.1)",
                "surface-container": "#201f1f",
                "surface": "#131313",
                "on-background": "#e5e2e1",
                "outline": "#999077",
                "inverse-on-surface": "#313030",
                "faction-android": "#DC143C",
                "on-tertiary": "#003353",
                "on-primary-fixed": "#221b00",
                "error-container": "#93000a",
                "on-surface": "#e5e2e1",
                "surface-tint": "#e9c400",
                "tertiary": "#f1f6ff",
                "secondary": "#66dd8b",
                "surface-container-low": "#1c1b1b",
                "on-primary-container": "#705e00",
                "inverse-surface": "#e5e2e1",
                "tertiary-fixed": "#cee5ff",
                "primary": "#fff6df",
                "surface-container-high": "#2a2a2a",
                "on-tertiary-container": "#206393",
                "tertiary-fixed-dim": "#96ccff",
                "secondary-container": "#25a55a",
                "on-secondary-container": "#003115",
                "secondary-fixed-dim": "#66dd8b",
                "on-secondary-fixed-variant": "#005227",
                "inverse-primary": "#705d00",
                "surface-variant": "#353534",
                "primary-container": "#ffd700",
                "on-secondary": "#003919",
                "on-tertiary-fixed": "#001d32",
                "surface-container-highest": "#353534",
                "secondary-fixed": "#83fba5",
                "on-error-container": "#ffdad6",
                "on-primary-fixed-variant": "#544600",
                "error": "#ffb4ab",
                "background": "#131313",
                "surface-dim": "#131313",
                "on-surface-variant": "#d0c6ab",
                "on-tertiary-fixed-variant": "#004a75",
                "gold-glow": "rgba(255, 215, 0, 0.4)",
                "obsidian-surface": "#1A1A1A",
                "on-secondary-fixed": "#00210c",
                "surface-bright": "#393939",
                "faction-human": "#4682B4"
            },
            borderRadius: {
                "DEFAULT": "0.125rem",
                "lg": "0.25rem",
                "xl": "0.5rem",
                "full": "0.75rem"
            },
            spacing: {
                "stack-lg": "32px",
                "stack-sm": "8px",
                "margin-mobile": "16px",
                "container-max": "1200px",
                "margin-desktop": "48px",
                "gutter": "24px",
                "stack-md": "16px"
            },
            fontFamily: {
                "body-md": ["Inter"],
                "headline-md": ["Playfair Display"],
                "headline-lg": ["Playfair Display"],
                "display-hero": ["Playfair Display"],
                "body-lg": ["Inter"],
                "label-sm": ["Inter"],
                "headline-lg-mobile": ["Playfair Display"],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                "body-md": ["16px", { lineHeight: "1.6", fontWeight: "400" }],
                "headline-md": ["24px", { lineHeight: "1.4", fontWeight: "600" }],
                "headline-lg": ["32px", { lineHeight: "1.3", fontWeight: "600" }],
                "display-hero": ["48px", { lineHeight: "1.2", letterSpacing: "-0.02em", fontWeight: "700" }],
                "body-lg": ["18px", { lineHeight: "1.6", fontWeight: "400" }],
                "label-sm": ["12px", { lineHeight: "1", letterSpacing: "0.05em", fontWeight: "600" }],
                "headline-lg-mobile": ["28px", { lineHeight: "1.2", fontWeight: "700" }]
            },
            keyframes: {
                shimmer: {
                    '100%': { transform: 'translateX(100%)' },
                }
            }
        },
    },
    plugins: [forms],
};
