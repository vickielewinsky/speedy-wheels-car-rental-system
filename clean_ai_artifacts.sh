#!/bin/bash

# Find and remove AI comments and symbols from PHP files
echo "Cleaning PHP files..."
find . -name "*.php" -type f -exec sed -i '
    # Remove File: headers
    s/^\/\/\s*File:.*$//g
    s/^#\s*File:.*$//g
    
    # Remove divider lines
    s/^\/\/\s*=.*$//g
    s/^\/\/\s*---.*$//g
    s/^#\s*=.*$//g
    s/^#\s*---.*$//g
    
    # Remove TODO: Note: comments
    s/\/\/\s*TODO:.*$//g
    s/\/\/\s*Note:.*$//g
    s/\/\/\s*TIP:.*$//g
    
    # Remove common AI section headers
    s/^\/\/\s*PROFESSIONAL.*$//g
    s/^\/\/\s*ENHANCED.*$//g
    s/^\/\/\s*ADVANCED.*$//g
    
    # Remove emojis and symbols
    s/[✨✅❌⭐🚀🔥📝🔧]//g
    s/[→←↑↓⇒⇐•◦▪■▫□]//g
    
    # Clean excessive whitespace
    s/^\s*$//g
' {} \;

# Also clean CSS files
echo "Cleaning CSS files..."
find . -name "*.css" -type f -exec sed -i '
    # Remove divider lines
    s/^\/\*\s*=.*\*\/$//g
    s/^\/\*\s*---.*\*\/$//g
    
    # Remove emojis
    s/[✨✅❌⭐🚀]//g
' {} \;

# Remove empty comment blocks
find . -name "*.php" -o -name "*.css" | xargs sed -i '/\/\/\s*$/d'

echo "Cleanup complete!"
