# NPK Valpeliste Plugin

WordPress plugin for displaying live puppy listings from pointer.datahound.no with real-time data fetching.

## Features

- **Real-time data**: Always fresh data from datahound.no API
- **No caching**: Instant updates when new puppies are added
- **Authenticated API**: Secure session-based authentication
- **Responsive design**: CSS Grid layout with mobile support
- **Badge system**: Automatic detection of breeding/elite status
- **Debug mode**: Comprehensive debugging tools for administrators

## Version History

- **v1.8.1** - Removed fallback functions, guaranteed authentic data only
- **v1.8** - Removed all caching for real-time updates
- **v1.7** - Fixed critical runtime errors and data structure validation
- **v1.6** - Enhanced badge detection and layout improvements
- **v1.5** - Initial stable release with cache system

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through WordPress admin
3. Configure settings under NPK Valpeliste in admin menu

## Usage

### Basic Shortcode
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
