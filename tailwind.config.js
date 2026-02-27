/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./views/**/*.blade.php'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: 'rgb(var(--kb-primary) / <alpha-value>)',
        'background-light': 'rgb(var(--kb-bg-light) / <alpha-value>)',
        'background-dark': 'rgb(var(--kb-bg-dark) / <alpha-value>)',
      },
      fontFamily: {
        display: ['var(--kb-font)', 'sans-serif'],
      },
      borderRadius: {
        DEFAULT: '0.25rem',
        lg: '0.5rem',
        xl: '0.75rem',
        full: '9999px',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
