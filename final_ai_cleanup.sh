#!/bin/bash

echo "=== Final AI Artifact Cleanup ==="

# 1. Remove PROFESSIONAL/ENHANCED comments from login.php
echo "Cleaning login.php..."
sed -i '/<!-- PROFESSIONAL FORM -->/,/<!-- END OF PROFESSIONAL FORM -->/d' src/modules/auth/login.php
sed -i '/PROFESSIONAL\|ENHANCED\|MAJOR\|COMPLETE/d' src/modules/auth/login.php

# 2. Clean CSS headers
echo "Cleaning CSS files..."
for css in src/assets/css/components/*.css; do
    [ -f "$css" ] || continue
    # Remove === comment blocks
    sed -i '/^\/\*\s*=/,/^=.*\*\//d' "$css"
    # Remove any remaining divider lines
    sed -i '/^[[:space:]]*\/\/[[:space:]]*[=-]\{10,\}/d' "$css"
done

# 3. Remove test scripts
echo "Removing test scripts..."
rm -f check_database_tables.php quick_email_test.php

# 4. Check for remaining issues
echo -e "\n=== Checking remaining issues ==="
echo "1. PROFESSIONAL/ENHANCED text:"
grep -r "PROFESSIONAL\|ENHANCED\|MAJOR\|COMPLETE" --include="*.php" --include="*.css" . 2>/dev/null | grep -v vendor

echo -e "\n2. Divider lines:"
grep -r "===\|---" --include="*.css" . 2>/dev/null

echo -e "\n3. Test scripts:"
ls check_database_tables.php quick_email_test.php 2>/dev/null && echo "Found!" || echo "None found ✓"

echo -e "\n=== Cleanup complete ==="
