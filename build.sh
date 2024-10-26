# Build drhanson.net using tailwind CSS + Hugo

# Fetch tailwind CSS
set -o xtrace
curl -sLO https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64
chmod +x tailwindcss-linux-x64
mv tailwindcss-linux-x64 tailwindcss

# Generate style.css and run Hugo
./tailwindcss -i assets/input.css -o assets/style.css
set +o xtrace
hugo build