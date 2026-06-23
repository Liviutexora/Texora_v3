/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './vendor/filament/**/*.blade.php', // Filament UI components
    './storage/framework/views/*.php',   // Compiled views
    './resources/**/*.ts',               // If using TypeScript
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
