# NPK Valpeliste WordPress Plugin v1.9.1

🏆 **Live badge system with zero caching - Production Ready!**

## 🎯 v1.9.1 UI Parity Update

### ✅ Forbedringer i denne versjonen:
- **UI Matching**: Ny `[npk_valpeliste]` shortcode bruker nå samme HTML-struktur og CSS-klasser som den gamle `[valpeliste]`
- **Identisk Layout**: Card structure, contact info, badges og annonsetekst er nå identisk
- **CSS Kompatibilitet**: Samme klasser som `valpeliste-container`, `valpeliste-card`, `valpeliste-badge` osv.
- **Kontaktinformasjon**: Fullstendig oppdretter-info med telefon, e-post og sted
- **Badge System**: Elite- og avlshund badges vises likt den gamle implementasjonen

### 🏗️ HTML Struktur (nå identisk):
```html
<div class="valpeliste-container">
  <div class="valpeliste-card-container">
    <h2 class="valpeliste-section-title approved">NPK Valpeliste</h2>
    <div class="valpeliste-card-group">
      <div class="valpeliste-card approved">
        <div class="valpeliste-card-top">
          <div class="valpeliste-card-header">
            <h3>Kennel Navn</h3>
            <span class="valpeliste-date">Forventet: 2025-02-15</span>
          </div>
          <div class="valpeliste-info">
            <div class="valpeliste-info-inner">
              <div class="valpeliste-info-row">
                <span class="valpeliste-label">Oppdretter:</span> Navn
              </div>
              <!-- Telefon, e-post osv. -->
            </div>
          </div>
        </div>
        <div class="valpeliste-card-body">
          <div class="valpeliste-parents">
            <div class="valpeliste-parent-row">
              <span class="valpeliste-label">Far:</span>
              <span class="valpeliste-parent-info">
                <span class="valpeliste-value">Hund Navn (RegNr)</span>
                <span class="valpeliste-badge elitehund">Elitehund</span>
              </span>
            </div>
            <!-- Mor struktur identisk -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

## 🚀 Overview

NPK Valpeliste is a WordPress plugin that displays live breeding data from the Norwegian Pointer Club (NPK) API with real-time elite and breeding dog badges. No caching, always fresh data.

## ✨ Features

- **Live Badge System**: Elite and breeding dog badges from individual NPK API calls
- **Zero Caching**: Fresh data on every page load - no WordPress transients or file storage
- **WordPress Integration**: Simple `[npk_valpeliste]` shortcode
- **Responsive Design**: Mobile-friendly card layout
- **Real-time Data**: Direct connection to NPK Datahound API
- **Error Handling**: Robust error handling and fallbacks

## 🔧 Installation

### 🚨 VIKTIG: Fatal Error Fix!
**Tidligere builds manglet kritiske filer og ga fatal error ved aktivering.**
**Løsning:** Bruk den korrigerte versjonen nedenfor!

1. **Download** the corrected production build: `builds/NPK_Valpeliste_v1.9.1_WordPress_Plugin.zip`
2. **Remove** any previous versions of the plugin
3. **Upload** to WordPress via `Plugins → Add New → Upload Plugin`
4. **Activate** the plugin
5. **Use** the shortcode `[npk_valpeliste]` in any page or post

**File size:** ~40K (includes all required files)
**PHP syntax:** Verified ✅

## 📋 Usage

```
// Simple shortcode usage
[npk_valpeliste]
```

The plugin will automatically:
- Connect to NPK API
- Fetch current litter data  
- Retrieve individual dog badges
- Display formatted results

## 🏆 Badge System

The plugin displays badges for:
- **Elite Dogs** (Elitehund) - Gold badge
- **Breeding Dogs** (Avlshund) - Green badge

Badges are fetched live from individual dog API calls for accurate status.

## 🔄 API Integration

- **Base URL**: `https://pointer.datahound.no`
- **Authentication**: Demo credentials (admin_username/admin_password)
- **Individual API**: `/admin/product/getdog?id=REGNR`
- **Zero Cache**: No WordPress transients, no file storage

## 📁 File Structure

```
npk_valpeliste.php              # Main plugin file
NPKDataExtractorLive.php        # Live API extractor
live_display_example.php        # Shortcode implementation
assets/
├── css/npk-styles.css         # Production styles
└── js/npk-scripts.js          # Production JavaScript
includes/                       # WordPress specific files
builds/                        # Production builds
```

## 🛠️ Development

```bash
# Build production version
./build.sh

# Test core functionality
php test_core.php
```

## 📊 Performance

- **API Response**: ~8-9 seconds (live individual calls)
- **Memory Usage**: ~0.5 MB
- **Zero Cache**: Fresh data every load
- **Mobile Optimized**: Responsive design

## 🎯 Problem Solved

**Before**: "Nå har ingen hunder avlshund eller elitehund markering!"
**After**: Working badge system with 7+ live badges displayed

## 📄 License

GPL-2.0+ - WordPress Plugin License

## 🤝 Support

For support and updates, see `DELIVERY_SUMMARY_v1.9.1.md`

---

**Status**: ✅ Production Ready - v1.9.1
**Last Updated**: August 5, 2025
