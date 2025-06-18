// tailwind.config.js
import typography from '@tailwindcss/typography';
import forms from '@tailwindcss/forms';
import aspectRatio from '@tailwindcss/aspect-ratio';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Light mode colors
        'light-bg': '#ffffff',
        'light-text': '#333333',
        'light-accent': '#3b82f6',
        
        // Dark mode colors
        'dark-bg': '#1e293b',
        'dark-text': '#f3f4f6',
        'dark-accent': '#60a5fa',
      }
    },
  },
  plugins: [
    typography,
    forms,
    aspectRatio,
  ],
}