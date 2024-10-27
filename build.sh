# Build using Tailwind CSS + Hugo

# Install tailwind CSS
npm install -D tailwindcss

# Generate style.css and run Hugo
npx tailwindcss -i assets/input.css -o assets/style.css
hugo build
