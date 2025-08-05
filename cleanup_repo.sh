#!/bin/bash

# NPK Valpeliste Repository Cleanup Script

echo "🧹 Rydder opp i NPK Valpeliste repository..."

# Create archive directory for test files
mkdir -p archive/tests
mkdir -p archive/documentation
mkdir -p archive/development

echo "📁 Arkiverer test- og utviklingsfiler..."

# Archive test files
mv test_*.php archive/tests/ 2>/dev/null
mv *test*.php archive/tests/ 2>/dev/null
mv debug_*.php archive/tests/ 2>/dev/null
mv analyze_*.php archive/tests/ 2>/dev/null
mv simple_*.php archive/tests/ 2>/dev/null
mv verify_*.php archive/tests/ 2>/dev/null
mv production_test.php archive/tests/ 2>/dev/null

# Archive demo files
mv demo_*.html archive/development/ 2>/dev/null

# Archive development files
mv search_*.php archive/development/ 2>/dev/null
mv enhanced_*.php archive/development/ 2>/dev/null
mv detailed_*.php archive/development/ 2>/dev/null
mv direct_*.php archive/development/ 2>/dev/null
mv dump_*.php archive/development/ 2>/dev/null
mv save_*.php archive/development/ 2>/dev/null
mv show_*.php archive/development/ 2>/dev/null
mv raw_*.php archive/development/ 2>/dev/null
mv find_*.php archive/development/ 2>/dev/null
mv investigate_*.php archive/development/ 2>/dev/null
mv complete_*.php archive/development/ 2>/dev/null
mv deep_*.php archive/development/ 2>/dev/null
mv badge_*.php archive/development/ 2>/dev/null
mv live_badge_*.php archive/development/ 2>/dev/null
mv api_structure_*.php archive/development/ 2>/dev/null
mv simplified_*.php archive/development/ 2>/dev/null
mv clear_cache.php archive/development/ 2>/dev/null

# Archive JSON exports
mv *.json archive/development/ 2>/dev/null

# Archive old documentation versions
mv DELIVERY_SUMMARY_v1.[0-8]*.md archive/documentation/ 2>/dev/null
mv DELIVERY_SUMMARY.md archive/documentation/ 2>/dev/null

# Archive development guides
mv BADGE_BREAKTHROUGH.md archive/documentation/ 2>/dev/null
mv CACHE_TESTING_GUIDE.md archive/documentation/ 2>/dev/null
mv ZERO_CACHE_GUIDE.md archive/documentation/ 2>/dev/null
mv EXTRACTOR_DOCUMENTATION.md archive/documentation/ 2>/dev/null
mv QUICK_START.md archive/documentation/ 2>/dev/null

# Keep only production files in root
echo "✅ Produksjonsfiler som beholdes i root:"
echo "  - npk_valpeliste.php (hovedplugin)"
echo "  - NPKDataExtractorLive.php (live API)"
echo "  - live_display_example.php (shortcode)"
echo "  - README.md (hoveddokumentasjon)"
echo "  - DELIVERY_SUMMARY_v1.9.1.md (siste leveranse)"
echo "  - build.sh (bygg-script)"
echo "  - assets/ (CSS/JS)"
echo "  - includes/ (WordPress filer)"
echo "  - builds/ (produksjonsbygg)"

# Update .gitignore for clean repo
echo "📝 Oppdaterer .gitignore..."

cat > .gitignore << 'EOF'
# NPK Valpeliste Plugin - Production Ready

# WordPress files
wp-config.php
.htaccess

# Logs
*.log
error_log
debug.log

# Temporary files
*.tmp
*.temp
/tmp/
cookies.txt
/archive/

# IDE files
.vscode/
.idea/
*.swp
*.swo

# macOS
.DS_Store
.DS_Store?
._*
.Spotlight-V100
.Trashes
ehthumbs.db

# Development files (archived)
test_*.php
*test*.php
debug_*.php
analyze_*.php
demo_*.html
search_*.php
DELIVERY_SUMMARY_v1.[0-8]*.md
DELIVERY_SUMMARY.md
*.json

# Keep only production essentials
!builds/
!assets/
!includes/
EOF

echo "🧹 Repository cleanup fullført!"
echo ""
echo "📂 Struktur etter opprydning:"
echo "├── npk_valpeliste.php (hovedplugin)"
echo "├── NPKDataExtractorLive.php (live API)"  
echo "├── live_display_example.php (shortcode)"
echo "├── README.md"
echo "├── DELIVERY_SUMMARY_v1.9.1.md"
echo "├── build.sh"
echo "├── assets/"
echo "├── includes/"
echo "├── builds/"
echo "└── archive/ (arkiverte filer)"
echo ""
echo "🚀 Repository er nå produksjonsklar for commit!"
