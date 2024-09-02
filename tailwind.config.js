/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [ "content/**/*.{html,md}",  "layouts/**/*.html", ],
    theme: {
        fontFamily: {
            'serif': [ 'Times', 'Times New Roman', 'ui-serif', 'Georgia', 'Cambria', 'serif' ],
            'sans': [ 'ui-sans-serif', 'system-ui', 'Arial', 'sans-serif' ],
        },
    },
    extend: {},
    plugins: [],
  }
