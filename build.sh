#!/bin/bash

# NPK Valpeliste Plugin Build Script v1.9
echo "🔨 Building NPK Valpeliste Plugin v1.9 for Production..."

# Get current directory name for plugin
PLUGIN_NAME="NPK_Valpeliste"
PLUGIN_VERSION="1.9"
VERSION="1.9"
BUILD_DIR="builds"
ZIP_NAME="${PLUGIN_NAME}_v${VERSION}.zip"
DIST_NAME="${PLUGIN_NAME}_v${VERSION}_WordPress_Plugin.zip"

# Create builds directory if it doesn't exist
mkdir -p "$BUILD_DIR"

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

# Create production and development builds
echo "📦 Creating production build..."

# Create temporary directory for clean build
TEMP_DIR="temp_build"
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR/$PLUGIN_NAME"

# Copy essential files only (exclude dev/test files)
echo "📁 Copying production files..."
cp -r includes "$TEMP_DIR/$PLUGIN_NAME/"
cp -r assets "$TEMP_DIR/$PLUGIN_NAME/"
cp npk_valpeliste.php "$TEMP_DIR/$PLUGIN_NAME/"
cp readme.txt "$TEMP_DIR/$PLUGIN_NAME/"
cp index.php "$TEMP_DIR/$PLUGIN_NAME/"
cp README.md "$TEMP_DIR/$PLUGIN_NAME/"
cp DELIVERY_SUMMARY_v1.9.md "$TEMP_DIR/$PLUGIN_NAME/"

# Create production ZIP
cd "$TEMP_DIR"
zip -r "../$BUILD_DIR/$DIST_NAME" "$PLUGIN_NAME"
cd ..

# Create development build with all files
echo "📦 Creating development build (with tests)..."
zip -r "$BUILD_DIR/$ZIP_NAME" . \
    -x "*.DS_Store" \
    -x "builds/*" \
    -x "temp_build/*" \
    -x ".git/*" \
    -x "cookies.txt"

# Cleanup
rm -rf "$TEMP_DIR"

if [[ $? -eq 0 ]]; then
    echo "🎉 Plugin builds created successfully!"
    echo ""
    echo "� Production Build: $BUILD_DIR/$DIST_NAME"
    echo "   � Size: $(du -h "$BUILD_DIR/$DIST_NAME" | cut -f1)"
    echo "   🎯 Ready for WordPress installation"
    echo ""
    echo "🔧 Development Build: $BUILD_DIR/$ZIP_NAME"
    echo "   📊 Size: $(du -h "$BUILD_DIR/$ZIP_NAME" | cut -f1)"
    echo "   🧪 Includes all test files and documentation"
    echo ""
    echo "🚀 Production deployment steps:"
    echo "1. Upload $DIST_NAME to WordPress"
    echo "2. Install via Plugins → Add New → Upload Plugin"
    echo "3. Activate 'NPK Valpeliste' plugin"
    echo "4. Use shortcode [valpeliste] in pages/posts"
    echo "5. Configure via WordPress Admin → NPK Valpeliste"
    echo ""
else
    echo "❌ Build failed!"
    exit 1
fi

echo ""
echo "✅ Build process completed!"
echo "🏠 Return to project directory: cd $(pwd)"
