#!/bin/bash
echo "=== Fixing ALL Files with Malformed Links ==="

# List of files to fix
files=(
    "src/modules/bookings/admin.php"
    "src/modules/bookings/index.php" 
    "src/modules/customers/index.php"
    "src/modules/notifications/index.php"
    "src/modules/vehicles/admin.php"
    "src/modules/vehicles/index.php"
    "src/includes/footer.php"
    "src/modules/auth/dashboard.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "Fixing: $file"
        
        # Create backup
        cp "$file" "${file}.backup"
        
        # Fix malformed links
        sed -i "s|href=\"/'\([^']*\)'\"|href=\"<?php echo base_url('\1'); ?>\"|g" "$file"
        
        echo "  ✓ Fixed"
    else
        echo "  ✗ File not found: $file"
    fi
done

echo "=== Adding url_helper.php to files that need it ==="

# Files that likely need url_helper.php
need_helper=(
    "src/modules/bookings/index.php"
    "src/modules/bookings/admin.php"
    "src/modules/customers/index.php"
    "src/modules/notifications/index.php"
    "src/modules/vehicles/admin.php"
    "src/modules/vehicles/index.php"
)

for file in "${need_helper[@]}"; do
    if [ -f "$file" ] && grep -q "base_url" "$file" && ! grep -q "url_helper.php" "$file"; then
        echo "Adding url_helper.php to: $file"
        
        # Find the right place to insert (after first require line)
        sed -i '0,/require_once/{s|require_once.*|&\nrequire_once __DIR__ . \"/../../helpers/url_helper.php\";|}' "$file"
        
        echo "  ✓ Added"
    fi
done

echo "=== Done ==="
