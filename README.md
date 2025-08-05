# NPK Valpeliste WordPress Plugin v1.9.1

🏆 **Live badge system with zero caching - Production Ready!**

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

1. **Download** the production build: `builds/NPK_Valpeliste_v1.9_WordPress_Plugin.zip`
2. **Upload** to WordPress via `Plugins → Add New → Upload Plugin`
3. **Activate** the plugin
4. **Use** the shortcode `[npk_valpeliste]` in any page or post

## 📋 Usage

```php
// Simple shortcode usage
[npk_valpeliste]
```
[valpeliste]
```

### With Debug Information
```
[valpeliste debug="yes"]
```

### Admin URL Parameters
```
?npk_debug=1  (administrators only)
```

## Technical Details

### API Integration
- **Endpoint**: `https://pointer.datahound.no/admin/product/getvalpeliste`
- **Authentication**: Session-based with demo/demo credentials
- **Data Format**: JSON with dogs array structure
- **Update Frequency**: Real-time (no caching)

### File Structure
```
NPK_Valpeliste/
├── npk_valpeliste.php         # Main plugin file
├── includes/
│   ├── admin-settings.php     # WordPress admin interface
│   ├── data-processing.php    # API authentication & data fetching
│   ├── rendering.php          # HTML generation & badge logic
│   └── helpers.php            # Utility functions
├── assets/
│   ├── css/
│   │   └── npk-valpeliste.css # Styling and responsive layout
│   └── js/
│       └── npk-valpeliste.js  # Interactive features
├── test_*.php                 # API testing utilities
└── docs/                      # Version documentation
```

## API Testing

Run `php analyze_api.php` to test the API connection and inspect data structure.

## Development

### Debug Mode
Enable debug mode to see:
- API authentication status
- Real-time data fetching details
- Badge detection logic
- Performance metrics

### Requirements
- WordPress 5.0+
- PHP 7.4+
- cURL extension
- Internet connection for API access

## License

GPL-2.0+

## Author

Developed by Erlendo for NPK (Norsk Pointer Klubb)
