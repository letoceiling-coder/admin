/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: 'hsl(var(--primary) / <alpha-value>)',
          foreground: 'hsl(var(--primary-foreground))',
        },
        ring: 'hsl(var(--ring))',
        accent: 'hsl(var(--accent) / <alpha-value>)',
        foreground: 'hsl(var(--foreground))',
        'muted-foreground': 'hsl(var(--muted-foreground))',
      },
    },
  },
  plugins: [],
}

