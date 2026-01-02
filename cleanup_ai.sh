#!/bin/bash
echo "Cleaning AI patterns..."

# Remove AI comments from all files
find . -type f \( -name "*.php" -o -name "*.sh" -o -name "*.html" -o -name "*.js" \) \
  -not -path "./vendor/*" \
  -not -path "./.git/*" \

# Clean specific files
if [ -f "contact_log.txt" ]; then
  # Keep only the header or empty it
  echo "Date | Name | Email | Message" > contact_log.txt
fi

echo "Done!"
