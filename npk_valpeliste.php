<?php
/**
 * Plugin Name: NPK Valpeliste
 * Plugin URI: https://pointer.no
 * Description: Viser NPK valpeliste med live badge data uten caching
 * Version: 1.9.1
 * Author: NPK Plugin Team
 * Author URI: 
 * Text Domain: npk-valpeliste
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NPK_VALPELISTE_VERSION', '1.9.1');
define('NPK_VALPELISTE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NPK_VALPELISTE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include core classes
require_once NPK_VALPELISTE_PLUGIN_DIR . 'NPKDataExtractorLive.php';
require_once NPK_VALPELISTE_PLUGIN_DIR . 'live_display_example.php';
require_once NPK_VALPELISTE_PLUGIN_DIR . 'includes/admin-settings.php';

// Include helper functions
require_once NPK_VALPELISTE_PLUGIN_DIR . 'includes/helpers.php';

// Include data processing functions
require_once NPK_VALPELISTE_PLUGIN_DIR . 'includes/data-processing.php';

// Include rendering functions
require_once NPK_VALPELISTE_PLUGIN_DIR . 'includes/rendering.php';

// Create includes directory if it doesn't exist
$includes_dir = NPK_VALPELISTE_PLUGIN_DIR . '/includes';
if (!file_exists($includes_dir)) {
    mkdir($includes_dir, 0755, true);
}

// Define required functions directly in the main file
// These will act as fallbacks if the includes fail to load

/**
 * Fallback function to safely get values from arrays/objects
 */
if (!function_exists('get_safe_value')) {
    function get_safe_value($data, $keys, $default = null) {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        
        foreach ($keys as $key) {
            if (is_object($data) && isset($data->$key)) {
                return $data->$key;
            } elseif (is_array($data) && isset($data[$key])) {
                return $data[$key];
            }
        }
        
        return $default;
    }
}

/**
 * Fallback function to check if entry is approved
 */
if (!function_exists('is_approved_entry')) {
    function is_approved_entry($valp) {
        $color = get_safe_value($valp, 'vplcolor', '');
        if ($color === '99CCFF') {
            return true;
        }
        return false;
    }
}

/**
 * Fallback error message if data processing functions are not loaded
 */
if (!function_exists('fetch_puppy_data')) {
    function fetch_puppy_data($force_refresh = false, $debug_mode = false) {
        $error_msg = '<div style="background:#ffebee;border:2px solid #f44336;padding:15px;margin:15px 0;border-radius:5px;">';
        $error_msg .= '<h4 style="color:#f44336;margin:0 0 10px 0;">❌ NPK Valpeliste Feil</h4>';
        $error_msg .= '<p><strong>Problem:</strong> Hovedfunksjoner for datahenting ikke lastet korrekt.</p>';
        $error_msg .= '<p><strong>Årsak:</strong> includes/data-processing.php ikke tilgjengelig.</p>';
        $error_msg .= '<p><strong>Løsning:</strong> Sjekk plugin-installasjon og filstruktur.</p>';
        if ($debug_mode) {
            $error_msg .= '<p><strong>Debug:</strong> Fallback-funksjon aktivert - ingen ekte API-tilgang.</p>';
        }
        $error_msg .= '</div>';
        
        if ($debug_mode) {
            return array(
                'debug' => $error_msg,
                'data' => array()
            );
        }
        
        return $error_msg;
    }
}

/**
 * Fallback function for data processing
 */
if (!function_exists('process_puppy_data')) {
    function process_puppy_data($data) {
        if (empty($data)) {
            return array('approved' => array(), 'other' => array());
        }
        
        $approved = array();
        $other = array();
        
        // Split data into approved and other
        foreach ($data as $item) {
            if (is_approved_entry($item)) {
                $approved[] = $item;
            } else {
                $other[] = $item;
            }
        }
        
        return array(
            'approved' => $approved,
            'other' => $other,
        );
    }
}

/**
 * Fallback function for parent data extraction
 */
if (!function_exists('extract_parent_data')) {
    function extract_parent_data($valp) {
        return array(
            'father_name' => get_safe_value($valp, ['FatherName', 'father_name', 'far_name'], 'N/A'),
            'father_reg' => get_safe_value($valp, ['father', 'far', 'FatherReg'], ''),
            'mother_name' => get_safe_value($valp, ['MotherName', 'mother_name', 'mor_name'], 'N/A'),
            'mother_reg' => get_safe_value($valp, ['mother', 'mor', 'MotherReg'], '')
        );
    }
}

// Function to generate card HTML (fallback)
if (!function_exists('generatePuppyCard')) {
    function generatePuppyCard($valp, $is_approved = false) {
        $output = '<div class="valpeliste-card' . ($is_approved ? ' approved' : '') . '">';
        $output .= '<div class="valpeliste-card-top">';
        $output .= '<div class="valpeliste-card-header">';
        $output .= '<h3>' . esc_html(get_safe_value($valp, 'kennel', 'N/A')) . '</h3>';
        $output .= '</div></div>';
        $output .= '<div class="valpeliste-card-body">';
        $output .= '<p>Father: ' . esc_html(get_safe_value($valp, 'FatherName', 'N/A')) . '</p>';
        $output .= '<p>Mother: ' . esc_html(get_safe_value($valp, 'MotherName', 'N/A')) . '</p>';
        $output .= '</div></div>';
        return $output;
    }
}

// Function to render listing HTML (fallback)
if (!function_exists('render_puppy_listing')) {
    function render_puppy_listing($processed_data, $debug_output = '') {
        $output = !empty($debug_output) ? $debug_output : '';
        $output .= '<div class="valpeliste-container">';
        
        $approved = isset($processed_data['approved']) ? $processed_data['approved'] : array();
        $other = isset($processed_data['other']) ? $processed_data['other'] : array();
        
        if (!empty($approved)) {
            $output .= '<h2>Approved Puppies</h2>';
            foreach ($approved as $puppy) {
                $output .= generatePuppyCard($puppy, true);
            }
        }
        
        if (!empty($other)) {
            $output .= '<h2>Other Puppies</h2>';
            foreach ($other as $puppy) {
                $output .= generatePuppyCard($puppy, false);
            }
        }
        
        $output .= '</div>';
        return $output;
    }
}

// Register scripts and styles
function npk_valpeliste_enqueue_scripts() {
    // Enqueue dashicons
    wp_enqueue_style('dashicons');
    
    // Enqueue CSS
    wp_enqueue_style('npk-valpeliste-css', plugins_url('assets/css/npk-valpeliste.css', __FILE__), array(), NPK_VALPELISTE_VERSION);
    
    // Enqueue JavaScript
    wp_enqueue_script('npk-valpeliste-js', plugins_url('assets/js/npk-valpeliste.js', __FILE__), array('jquery'), NPK_VALPELISTE_VERSION, true);
}
add_action('wp_enqueue_scripts', 'npk_valpeliste_enqueue_scripts');

// Main shortcode function
function hent_valper_shortcode($atts = []) {
    // Parse attributes
    $atts = shortcode_atts([
        'debug' => 'no',
    ], $atts);
    
    $debug_mode = ($atts['debug'] === 'yes');
    
    // Check for URL parameters for debug mode (for admin users only)
    if (current_user_can('manage_options')) {
        if (isset($_GET['npk_debug']) && $_GET['npk_debug'] === '1') {
            $debug_mode = true;
        }
    }
    
    // Verify functions exist before calling them
    if (!function_exists('fetch_puppy_data')) {
        if ($debug_mode) {
            return '<div class="error">Error: Required function fetch_puppy_data() not found. Check plugin installation.</div>';
        } else {
            return '<div class="error">Error loading puppy data. Please contact administrator.</div>';
        }
    }
    
    // Fetch fresh data (always real-time)
    $response = fetch_puppy_data(false, $debug_mode);
    
    // Handle debug response
    if ($debug_mode && is_array($response) && isset($response['debug'])) {
        $debug_output = $response['debug'];
        $data = $response['data'];
    } else if ($debug_mode && is_string($response)) {
        // Error message with debug info
        return $response;
    } else {
        $debug_output = '';
        $data = $response;
    }

    // Konverter til ny individstruktur før videre prosessering
    if (function_exists('convert_to_individual_structure')) {
        // Extract dogs array from API response if needed
        if (is_array($data) && isset($data['dogs']) && is_array($data['dogs'])) {
            $data = convert_to_individual_structure($data['dogs']);
        } else if (is_array($data) && !isset($data['dogs'])) {
            // Data is already an array of dogs - make sure each element is an array
            $is_dogs_array = true;
            foreach ($data as $item) {
                if (!is_array($item)) {
                    $is_dogs_array = false;
                    break;
                }
            }
            if ($is_dogs_array) {
                $data = convert_to_individual_structure($data);
            } else {
                // Invalid data structure
                if ($debug_mode) {
                    $debug_output .= '<div class="error">Error: Data structure is not an array of dogs</div>';
                }
                $data = array();
            }
        } else {
            // Invalid data structure
            if ($debug_mode) {
                $debug_output .= '<div class="error">Error: Invalid data structure for convert_to_individual_structure() - data type: ' . gettype($data) . '</div>';
            }
            $data = array();
        }
    }

    // Process data
    if (!function_exists('process_puppy_data')) {
        if ($debug_mode) {
            return $debug_output . '<div class="error">Error: Required function process_puppy_data() not found.</div>';
        } else {
            return '<div class="error">Error processing puppy data. Please contact administrator.</div>';
        }
    }
    
    $processed_data = process_puppy_data($data);
    
    // Render the puppy listing
    if (!function_exists('render_puppy_listing')) {
        if ($debug_mode) {
            return $debug_output . '<div class="error">Error: Required function render_puppy_listing() not found.</div>';
        } else {
            return '<div class="error">Error rendering puppy data. Please contact administrator.</div>';
        }
    }
    
    $html = render_puppy_listing($processed_data, $debug_output);
    return $html;
}

// Make sure shortcode is registered after WordPress has fully loaded
add_action('init', function() {
    // Register the shortcodes
    add_shortcode('valpeliste', 'hent_valper_shortcode');
    add_shortcode('hent_valper', 'hent_valper_shortcode'); // Optional alias
    
    // Log that shortcodes have been registered
    error_log('NPK Valpeliste: Shortcodes registered');
});

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'npk_valpeliste_activate');
register_deactivation_hook(__FILE__, 'npk_valpeliste_deactivate');

/**
 * Plugin activation function
 */
function npk_valpeliste_activate() {
    // Clear caches
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Force WordPress to detect the shortcode
    add_shortcode('valpeliste', 'hent_valper_shortcode');
    
    // Log activation
    error_log('NPK Valpeliste: Plugin activated');
}

// Consider adding this to force a flush on every load during development
add_action('init', function() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Clear object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
}, 1);

/**
 * Plugin deactivation function
 */
function npk_valpeliste_deactivate() {
    // Clean up any transient data
    delete_transient('pointer_valpeliste_live_data');
    delete_transient('pointer_valpeliste_html');
}