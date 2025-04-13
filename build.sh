# Build using Tailwind CSS + Hugo

# Install Tailwind CSS and Tailwind CLI
npm install tailwindcss @tailwindcss/cli

# Generate style.css and run Hugo
npx @tailwindcss/cli -i assets/input.css -o assets/style.css
hugo build
