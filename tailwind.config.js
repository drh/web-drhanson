/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [ "content/**/*.{html,md}",  "layouts/**/*.html", ],
    theme: {
        fontFamily: {
            'serif': [ 'Times', 'Times New Roman', 'ui-serif', 'Georgia', 'Cambria', 'serif' ],
            'sans': [ 'ui-sans-serif', 'system-ui', 'Arial', 'sans-serif' ],
        },
        screens: {
            'xl': '1080px',
            // => @media (min-width: 1080px) { ... }
        },
    },
    extend: {},
    plugins: [],
  }
