#!/bin/bash

# NPK Valpeliste Plugin Build Script
echo "🔨 Building NPK Valpeliste Plugin for Production..."

# Get current directory name for plugin
PLUGIN_NAME="NPK_Valpeliste"
VERSION="1.7"
BUILD_DIR="../../../.."
ZIP_NAME="${PLUGIN_NAME}_v${VERSION}.zip"

# Clean up test files and temporary files
echo "🧹 Cleaning up test files..."
rm -f function-test.php
rm -f api-test-simple.php
rm -f simple-test.php
rm -f quick-api-test.php
rm -f test-*.php
rm -f *-test-*.php
rm -f test-output.txt
rm -f api-test-result.txt
rm -f verify.php
rm -f verification.log

# Check required files
echo "📋 Checking required files..."
required_files=(
    "npk_valpeliste.php"
    "includes/helpers.php"
    "includes/data-processing.php"
    "includes/rendering.php"
    "includes/admin-settings.php"
    "assets/css/npk-valpeliste.css"
    "assets/js/npk-valpeliste.js"
    "readme.txt"
    "index.php"
)

all_present=true
for file in "${required_files[@]}"; do
    if [[ -f "$file" ]]; then
        echo "✅ $file"
    else
        echo "❌ Missing: $file"
        all_present=false
    fi
done

if ! $all_present; then
    echo "❌ Build failed - missing required files"
    exit 1
fi

# Create zip file
echo "📦 Creating zip file..."
cd ..
zip -r "$ZIP_NAME" "$PLUGIN_NAME" \
    -x "*.DS_Store" \
    -x "$PLUGIN_NAME/build.sh" \
    -x "$PLUGIN_NAME/*test*" \
    -x "$PLUGIN_NAME/*.log"

if [[ $? -eq 0 ]]; then
    echo "🎉 Plugin ZIP created successfully!"
    echo "📁 File: $ZIP_NAME"
    echo "📍 Location: $(pwd)/$ZIP_NAME"
    echo ""
    echo "🚀 Ready for production deployment:"
    echo "1. Upload $ZIP_NAME to pointer.no"
    echo "2. Extract to wp-content/plugins/"
    echo "3. Activate plugin in WordPress admin"
    echo "4. Use shortcode [valpeliste] on pages/posts"
    echo "5. Configure settings under Settings > NPK Valpeliste"
    echo ""
    echo "📊 File size: $(du -h "$ZIP_NAME" | cut -f1)"
    ls -la "$ZIP_NAME"
else
    echo "❌ Failed to create ZIP file"
    exit 1
fi
