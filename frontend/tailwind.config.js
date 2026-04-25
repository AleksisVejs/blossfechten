/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        parchment: {
          50:  '#faf5e8',
          100: '#f3e9d2',
          200: '#e7d6ad',
          300: '#d9bf85',
          400: '#c6a25f',
          500: '#a07a3a',
          600: '#7a5a2a',
          700: '#5a421f',
        },
        ink: {
          50:  '#f5f4f1',
          500: '#3a342c',
          900: '#1a1a1a',
        },
        oxblood: {
          500: '#7a1f1f',
          600: '#5f1616',
          700: '#421010',
        },
        gold: {
          400: '#c9a24c',
          500: '#a8852f',
        },
      },
      fontFamily: {
        serif: ['"Cormorant Garamond"', 'Georgia', 'serif'],
        display: ['"UnifrakturMaguntia"', '"Cormorant Garamond"', 'serif'],
        sans: ['"Inter"', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
