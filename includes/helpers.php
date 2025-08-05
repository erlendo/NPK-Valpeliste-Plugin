<?php
/* filepath: /Users/erlendo/Local Sites/pointerdatabasen/app/public/wp-content/plugins/NPK_Valpeliste/includes/helpers.php */

/**
 * Helper functions for NPK Valpeliste plugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Safely get a value from complex object or array with fallback
 * @param mixed $data The data structure (object or array)
 * @param string|array $keys The key or keys to try
 * @param mixed $default Default value if not found
 * @return mixed The found value or default
 */
if (!function_exists('get_safe_value')) {
    function get_safe_value($data, $keys, $default = null) {
        // Handle both string keys and arrays of fallback keys
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        
        foreach ($keys as $key_path) {
            $current_data = $data;
            
            // Handle nested paths like ['father', 'FatherReg']
            if (is_array($key_path)) {
                foreach ($key_path as $key) {
                    if (is_object($current_data) && isset($current_data->$key)) {
                        $current_data = $current_data->$key;
                    } elseif (is_array($current_data) && isset($current_data[$key])) {
                        $current_data = $current_data[$key];
                    } else {
                        $current_data = null;
                        break;
                    }
                }
                if ($current_data !== null) {
                    return $current_data;
                }
            } else {
                // Handle simple keys
                if (is_object($data) && isset($data->$key_path)) {
                    return $data->$key_path;
                } elseif (is_array($data) && isset($data[$key_path])) {
                    return $data[$key_path];
                }
            }
        }
        
        return $default;
    }
}

/**
 * Determine if a puppy entry is approved
 * @param array|object $valp Puppy data
 * @return bool Whether the entry is approved
 */
if (!function_exists('is_approved_entry')) {
    function is_approved_entry($valp) {
        // Check for color code (most reliable indicator)
        $color = get_safe_value($valp, 'vplcolor', '');
        if ($color === '99CCFF') {
            return true;
        }
        
        // Check other possible indicators
        $approved = get_safe_value($valp, ['approved', 'godkjent'], false);
        if ($approved) {
            return true;
        }
        
        return false;
    }
}

/**
 * Determine avlshund and elitehund status for dogs
 * @param array|object $valp Puppy data
 * @param string $parent Which parent to check: 'father', 'mother'
 * @param bool $debug_mode Whether to include debug information
 * @return array Status and reason information
 */
if (!function_exists('get_dog_status')) {
function get_dog_status($valp, $parent = 'father', $debug_mode = false) {
    // Default return structure
    $result = [
        'avlshund' => false,
        'elitehund' => false,
        'avlshund_reason' => '',
        'elitehund_reason' => '',
    ];
    
    // Get parent name and registration info
    $dog_reg = '';
    $dog_name = '';
    if ($parent == 'father') {
        $father_data = get_safe_value($valp, ['father'], []);
        $dog_reg = get_safe_value($father_data, ['FatherReg'], '');
        if (empty($dog_reg)) {
            $dog_reg = get_safe_value($valp, ['FatherReg'], '');
        }
        
        $dog_name = get_safe_value($father_data, ['FatherName'], '');
        if (empty($dog_name)) {
            $dog_name = get_safe_value($valp, ['FatherName'], '');
        }
    } else {
        $mother_data = get_safe_value($valp, ['mother'], []);
        $dog_reg = get_safe_value($mother_data, ['MotherReg'], '');
        if (empty($dog_reg)) {
            $dog_reg = get_safe_value($valp, ['MotherReg'], '');
        }
        
        $dog_name = get_safe_value($mother_data, ['MotherName'], '');
        if (empty($dog_name)) {
            $dog_name = get_safe_value($valp, ['MotherName'], '');
        }
    }
    
    // Get configuration settings with defaults
    $criteria_config = get_option('npk_valpeliste_criteria', [
        'override_dogs' => []
    ]);
    
    // Safety check for configuration
    if (!is_array($criteria_config)) {
        $criteria_config = ['override_dogs' => []];
    }
    if (!isset($criteria_config['override_dogs']) || !is_array($criteria_config['override_dogs'])) {
        $criteria_config['override_dogs'] = [];
    }
    
    // Specific fixes for problematic dogs
    $excluded_dogs = array(
        "Brennmoen's Mattis", 
        "Sølenriket's E- Bella",
    );
    
    if (in_array($dog_name, $excluded_dogs)) {
        if ($debug_mode) {
            $result['debug_info']['excluded_dog'] = "This dog is specifically excluded from badges";
        }
        return $result;
    }
    
    // Skip manual overrides for now to debug badge system
    // Ny implementering: Bruker forbehandlede data fra data-processing.php
    $parent_data = get_safe_value($valp, [$parent], []);
    
    if (empty($parent_data)) {
        if ($debug_mode) {
            $result['debug_info']['error'] = "Ingen parent data funnet for $parent";
        }
        return $result;
    }
    
    $avlsh_value = get_safe_value($parent_data, ['avlsh'], '');
    $eliteh_value = get_safe_value($parent_data, ['eliteh'], '');
    
    // Debug info
    if ($debug_mode) {
        $result['debug_info'] = [
            'parent' => $parent,
            'parent_data' => $parent_data,
            'avlsh_value' => $avlsh_value,
            'eliteh_value' => $eliteh_value,
            'dog_name' => $dog_name,
            'dog_reg' => $dog_reg
        ];
    }
    
    // Sjekk avlshund status (ny logikk fra data-processing.php)
    if ($avlsh_value == '1' || $avlsh_value === 1) {
        $result['avlshund'] = true;
        $result['avlshund_reason'] = 'Individuell avlshund status (API)';
    }
    
    // Sjekk elitehund status (ny logikk fra data-processing.php)  
    if ($eliteh_value == '1' || $eliteh_value === 1) {
        $result['elitehund'] = true;
        $result['elitehund_reason'] = 'Individuell elitehund status (API)';
    }
    
    return $result;
}
}

/**
 * Parse and render premium ribbon HTML from Datahound API
 * @param string $ribbon_html Raw HTML from FatherPrem/MotherPrem fields
 * @return string Processed HTML for display
 */
if (!function_exists('parse_premium_ribbons')) {
    function parse_premium_ribbons($ribbon_html) {
        if (empty($ribbon_html)) {
            return '';
        }
        
        $output = '';
        
        // Extract all img tags from the ribbon HTML
        if (preg_match_all('/<img[^>]*>/i', $ribbon_html, $matches)) {
            foreach ($matches[0] as $img_tag) {
                // Extract tooltip text
                $tooltip = '';
                if (preg_match('/qtip=["\']([^"\']*)["\']/', $img_tag, $qtip_matches)) {
                    $tooltip = $qtip_matches[1];
                }
                
                // Extract image source
                $src = '';
                if (preg_match('/src=["\']([^"\']*)["\']/', $img_tag, $src_matches)) {
                    $src = $src_matches[1];
                    
                    // Convert relative URLs to absolute URLs for Datahound domain
                    if (strpos($src, '/') === 0) {
                        $src = 'https://pointer.datahound.no' . $src;
                    }
                }
                
                // Determine badge type from image source or tooltip
                $badge_class = 'ribbon-badge';
                $badge_text = $tooltip;
                
                if (stripos($src, 'ribbon_red') !== false || stripos($tooltip, 'utstilling') !== false) {
                    $badge_class .= ' ribbon-exhibition';
                    if (empty($badge_text)) $badge_text = 'Utstillingspremie';
                } elseif (stripos($src, 'ribbon_darkblue') !== false || stripos($tooltip, 'jakt') !== false) {
                    $badge_class .= ' ribbon-hunt';
                    if (empty($badge_text)) $badge_text = 'Jaktpremie';
                } elseif (stripos($src, 'ribbon_blue') !== false) {
                    $badge_class .= ' ribbon-blue';
                    if (empty($badge_text)) $badge_text = 'Blå bånd';
                } elseif (stripos($src, 'ribbon_yellow') !== false) {
                    $badge_class .= ' ribbon-yellow';
                    if (empty($badge_text)) $badge_text = 'Gult bånd';
                }
                
                // Create enhanced ribbon display
                if (!empty($src)) {
                    $output .= '<span class="' . htmlspecialchars($badge_class, ENT_QUOTES) . '" title="' . htmlspecialchars($badge_text, ENT_QUOTES) . '">';
                    $output .= '<img src="' . htmlspecialchars($src, ENT_QUOTES) . '" alt="' . htmlspecialchars($badge_text, ENT_QUOTES) . '" class="ribbon-image" />';
                    $output .= '<span class="ribbon-text">' . htmlspecialchars($badge_text, ENT_QUOTES) . '</span>';
                    $output .= '</span>';
                } else {
                    // Fallback if no image source found
                    $output .= '<span class="' . htmlspecialchars($badge_class, ENT_QUOTES) . '" title="' . htmlspecialchars($badge_text, ENT_QUOTES) . '">';
                    if (stripos($badge_text, 'utstilling') !== false) {
                        $output .= '🏵️ ';
                    } elseif (stripos($badge_text, 'jakt') !== false) {
                        $output .= '🎖️ ';
                    } else {
                        $output .= '🏆 ';
                    }
                    $output .= htmlspecialchars($badge_text, ENT_QUOTES);
                    $output .= '</span>';
                }
            }
        }
        
        return $output;
    }
}