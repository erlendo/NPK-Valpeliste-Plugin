<?php
/**
 * Plugin Name: Pointer Valpeliste
 * Plugin URI: https://pointer.no
 * Description: En shortcode for å vise valpeliste fra pointer.datahound.no med inline badge-layout
 * Version:           1.9.10
 * Author: Erlendo
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

// Define plugin constants with safety checks
if (!defined('NPK_VALPELISTE_VERSION')) {
    define('NPK_VALPELISTE_VERSION', '1.9.10');
}
if (!defined('NPK_VALPELISTE_PLUGIN_DIR')) {
    define('NPK_VALPELISTE_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('NPK_VALPELISTE_PLUGIN_URL')) {
    define('NPK_VALPELISTE_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Include admin settings with error handling
if (file_exists(NPK_VALPELISTE_PLUGIN_DIR . 'includes/admin-settings.php')) {
    require_once NPK_VALPELISTE_PLUGIN_DIR . 'includes/admin-settings.php';
} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>NPK Valpeliste: Missing admin-settings.php file</p></div>';
    });
}

// Include helper functions with error handling
if (file_exists(NPK_VALPELISTE_PLUGIN_DIR . 'includes/helpers.php')) {
    require_once NPK_VALPELISTE_PLUGIN_DIR . 'includes/helpers.php';
} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>NPK Valpeliste: Missing helpers.php file</p></div>';
    });
}

// Include data processing functions with error handling
if (file_exists(NPK_VALPELISTE_PLUGIN_DIR . 'includes/data-processing.php')) {
    require_once NPK_VALPELISTE_PLUGIN_DIR . 'includes/data-processing.php';
} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>NPK Valpeliste: Missing data-processing.php file</p></div>';
    });
}

// Include rendering functions with error handling
if (file_exists(NPK_VALPELISTE_PLUGIN_DIR . 'includes/rendering.php')) {
    require_once NPK_VALPELISTE_PLUGIN_DIR . 'includes/rendering.php';
} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>NPK Valpeliste: Missing rendering.php file</p></div>';
    });
}

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
if (!function_exists('npk_valpeliste_enqueue_scripts')) {
    function npk_valpeliste_enqueue_scripts() {
        // Enqueue dashicons
        wp_enqueue_style('dashicons');
        
        // Enqueue CSS
        wp_enqueue_style('npk-valpeliste-css', plugins_url('assets/css/npk-valpeliste.css', __FILE__), array(), NPK_VALPELISTE_VERSION);
        
        // Enqueue JavaScript
        wp_enqueue_script('npk-valpeliste-js', plugins_url('assets/js/npk-valpeliste.js', __FILE__), array('jquery'), NPK_VALPELISTE_VERSION, true);
    }
}
add_action('wp_enqueue_scripts', 'npk_valpeliste_enqueue_scripts');

// Main shortcode function
if (!function_exists('hent_valper_shortcode')) {
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
}

/**
 * New shortcode function using NPKDataExtractorLive for complete data
 */
if (!function_exists('npk_valpeliste_shortcode')) {
    function npk_valpeliste_shortcode($atts = []) {
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
        
        // Try to use the new live display function
        if (file_exists(NPK_VALPELISTE_PLUGIN_DIR . 'live_display_example.php')) {
            require_once NPK_VALPELISTE_PLUGIN_DIR . 'live_display_example.php';
            
            if (function_exists('npk_get_live_data') && function_exists('npk_display_valpeliste_from_data')) {
                try {
                    $data = npk_get_live_data();
                    
                    if (isset($data['error'])) {
                        if ($debug_mode) {
                            return '<div class="npk-error">NPK API Error: ' . esc_html($data['error']) . '</div>';
                        } else {
                            return '<div class="npk-error">Could not load puppy data at this time.</div>';
                        }
                    }
                    
                    return npk_display_valpeliste_from_data($data);
                    
                } catch (Exception $e) {
                    if ($debug_mode) {
                        return '<div class="npk-error">Exception: ' . esc_html($e->getMessage()) . '</div>';
                    } else {
                        return '<div class="npk-error">Could not load puppy data.</div>';
                    }
                }
            }
        }
        
        // Fallback to old shortcode if new one fails
        if ($debug_mode) {
            return '<div class="npk-notice">Falling back to old shortcode method</div>' . hent_valper_shortcode($atts);
        } else {
            return hent_valper_shortcode($atts);
        }
    }
}

// Make sure shortcode is registered after WordPress has fully loaded
add_action('init', function() {
    // Prevent multiple registrations from duplicate plugins
    static $npk_shortcodes_registered = false;
    if ($npk_shortcodes_registered) {
        return;
    }
    
    // Register the shortcodes only if they don't exist
    if (!shortcode_exists('valpeliste')) {
        add_shortcode('valpeliste', 'hent_valper_shortcode');
    }
    if (!shortcode_exists('hent_valper')) {
        add_shortcode('hent_valper', 'hent_valper_shortcode'); // Optional alias
    }
    if (!shortcode_exists('npk_valpeliste')) {
        add_shortcode('npk_valpeliste', 'npk_valpeliste_shortcode'); // New shortcode
    }
    
    $npk_shortcodes_registered = true;
    
    // Log only once and only in debug mode
    if (defined('WP_DEBUG') && WP_DEBUG) {
        static $npk_logged_once = false;
        if (!$npk_logged_once) {
            error_log('NPK Valpeliste: Shortcodes registered (v1.9.10)');
            $npk_logged_once = true;
        }
    }
});

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'npk_valpeliste_activate');
register_deactivation_hook(__FILE__, 'npk_valpeliste_deactivate');

/**
 * Plugin activation function
 */
if (!function_exists('npk_valpeliste_activate')) {
    function npk_valpeliste_activate() {
        // Clear caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Log activation only in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('NPK Valpeliste: Plugin activated');
        }
    }
}

// Cache flush only in debug mode and only once per request
add_action('init', function() {
    static $cache_flushed = false;
    if (defined('WP_DEBUG') && WP_DEBUG && !$cache_flushed) {
        // Clear object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        $cache_flushed = true;
    }
}, 999); // Lower priority to run after shortcodes

/**
 * Plugin deactivation function
 */
if (!function_exists('npk_valpeliste_deactivate')) {
    function npk_valpeliste_deactivate() {
        // Clean up any transient data
        delete_transient('pointer_valpeliste_live_data');
        delete_transient('pointer_valpeliste_html');
    }
}