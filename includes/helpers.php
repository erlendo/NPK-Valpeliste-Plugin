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
    
    // Get configuration settings with defaults
    $criteria_config = get_option('npk_valpeliste_criteria', [
        'strict_mode' => true,   // If true, only use avlsh="1" flag - default to strict mode for safety
        'use_hd_status' => false, // Use HD status as criterion - turning off by default
        'use_name_check' => false, // Check if "avlsh" exists in dog's name - turning off by default
        'use_field_checks' => false, // Check other fields that may indicate avlshund - turning off by default
        'use_jakt_scores' => true, // Use jakt score for elite status
        'hd_threshold' => 100,  // HD score threshold
        'jakt_threshold' => 115, // Jakt score threshold
        'override_dogs' => []    // Liste med hunder som skal overstyres
    ]);
    
    // Sjekk om denne hunden skal overstyres manuelt
    $dog_reg = '';
    $dog_name = '';
    if ($parent == 'father') {
        $dog_reg = get_safe_value($valp, ['father', 'FatherReg'], '');
        $dog_name = get_safe_value($valp, ['FatherName', 'father'], '');
    } else {
        $dog_reg = get_safe_value($valp, ['mother', 'MotherReg'], '');
        $dog_name = get_safe_value($valp, ['MotherName', 'mother'], '');
    }
    
    // Specific fixes for problematic dogs
    // Check the exact names of dogs that should NOT have the badge
    $excluded_dogs = array(
        "Brennmoen's Mattis", 
        "Sølenriket's E- Bella",
        // Add more dogs here as needed
    );
    
    if (in_array($dog_name, $excluded_dogs)) {
        if ($debug_mode) {
            $result['debug_info']['excluded_dog'] = "This dog is specifically excluded from badges";
        }
        return $result; // Return early with no badges
    }
    
    $override_dogs = !empty($criteria_config['override_dogs']) ? $criteria_config['override_dogs'] : [];
    if (!empty($dog_reg) && is_array($override_dogs) && isset($override_dogs[$dog_reg])) {
        $override = $override_dogs[$dog_reg];
        if (isset($override['avlshund'])) {
            $result['avlshund'] = (bool)$override['avlshund'];
            $result['avlshund_reason'] = 'Manuell overstyring';
        }
        if (isset($override['elitehund'])) {
            $result['elitehund'] = (bool)$override['elitehund'];
            $result['elitehund_reason'] = 'Manuell overstyring';
        }
        
        if ($debug_mode) {
            $result['manual_override'] = true;
        }
        
        // Hvis total overstyring, returner resultatet direkte
        if (!empty($override['total_override'])) {
            return $result;
        }
    }
    
    // Field mappings based on parent type
    $field_prefix = ($parent == 'father') ? 'father' : 'mother';
    
    // Først sjekker vi om valpen har 'avlsh' for far og 'avlshM' for mor
    // Men undersøkelse viser at databasen bruker 'avlsh' for begge,
    // så derfor sjekker vi begge feltene for sikkerhetsskyld
    $flag_field = 'avlsh'; 
    $elite_field = ($parent == 'father') ? 'eliteh' : 'elitehM';
    $hd_field = $field_prefix . 'HD';
    $jakt_field = 'jaktind' . ($parent == 'father' ? 'F' : 'M');
    $name_field = ($parent == 'father') ? ['FatherName', 'father'] : ['MotherName', 'mother'];
    $status_prefix = ($parent == 'father') ? ['far', 'father'] : ['mor', 'mother'];
    
    // Get parent name for checking
    $parent_name = strtolower(get_safe_value($valp, $name_field, ''));
    
    // Debugging - la oss lagre verdien av alle felt som kan være relevante
    if ($debug_mode) {
        $debug_fields = [
            'avlsh', 'avlshM', 'eliteh', 'elitehM', 
            'fatherHD', 'motherHD', 'FatherPrem', 'MotherPrem',
            'althdF', 'althdM', 'jaktindF', 'jaktindM'
        ];
        
        $result['debug_info'] = [];
        foreach ($debug_fields as $field) {
            if (is_string($field) && isset($valp[$field])) {
                $result['debug_info'][$field] = $valp[$field];
            } else {
                $result['debug_info'][$field] = 'ikke satt';
            }
        }
        
        // Sjekk også FatherName og MotherName
        $result['debug_info']['FatherName'] = get_safe_value($valp, 'FatherName', 'ikke satt');
        $result['debug_info']['MotherName'] = get_safe_value($valp, 'MotherName', 'ikke satt');
    }
    
    // Sjekk først om strengt modus er aktivert
    $strict_mode = !empty($criteria_config['strict_mode']);
    
    // 1. Check direct avlsh flag - this always has priority
    if (is_string($flag_field) && isset($valp[$flag_field]) && $valp[$flag_field] == '1') {
        $result['avlshund'] = true;
        $result['avlshund_reason'] = $flag_field . '="1"';
    }
    
    if (is_string($elite_field) && isset($valp[$elite_field]) && $valp[$elite_field] == '1') {
        $result['elitehund'] = true;
        $result['elitehund_reason'] = $elite_field . '="1"';
    }
    
    // Stop here if strict mode is enabled
    if ($strict_mode) {
        if ($debug_mode) {
            $result['strict_mode_active'] = true;
        }
        return $result;
    }
    
    // 2. HD status check
    if (!empty($criteria_config['use_hd_status'])) {
        $hd_threshold = !empty($criteria_config['hd_threshold']) ? intval($criteria_config['hd_threshold']) : 100;
        
        // Sjekk både direkte feltene og alternativene
        $hd_fields = [$hd_field];
        if ($parent == 'father') {
            $hd_fields[] = 'althdF';
            $hd_fields[] = 'althdFather';
        } else {
            $hd_fields[] = 'althdM';
            $hd_fields[] = 'althdMother';
        }
        
        foreach ($hd_fields as $curr_hd_field) {
            if (is_string($curr_hd_field) && isset($valp[$curr_hd_field]) && !empty($valp[$curr_hd_field])) {
                // Sjekk om verdien er numerisk og over terskelen
                $hd_value = $valp[$curr_hd_field];
                if (is_numeric($hd_value) && intval($hd_value) > $hd_threshold) {
                    $result['avlshund'] = true;
                    $result['avlshund_reason'] = $curr_hd_field . '=' . $hd_value;
                    break; // Stopp etter første match
                }
            }
        }
    }
    
    // 3. Check for "avlshund" in name - be more precise to avoid false positives
    if (!empty($criteria_config['use_name_check']) && !empty($parent_name)) {
        // Create a more specific check for "avlshund" in the name
        // We're looking for the exact word "avlshund" or "avlsh"
        if (preg_match('/\bavlshund\b/i', $parent_name) || preg_match('/\bavlsh\b/i', $parent_name)) {
            $result['avlshund'] = true;
            $result['avlshund_reason'] = 'navn inneholder "avlshund"';
        }
        // Similarly for "elite"
        if (preg_match('/\belite\b/i', $parent_name)) {
            $result['elitehund'] = true;
            $result['elitehund_reason'] = 'navn inneholder "elite"';
        }
    }
    
    // 4. Check related fields for status indicators
    if (!empty($criteria_config['use_field_checks'])) {
        $status_fields = [
            'FatherPrem', 'MotherPrem', 'comments', 'vpltooltip', 
            'premie', 'PremieM', 'jakt', 'jaktM', 'althdF', 'althdM'
        ];
        
        foreach ($status_fields as $field) {
            if (is_string($field) && isset($valp[$field]) && !empty($valp[$field])) {
                $value = strtolower($valp[$field]);
                
                // Check for avlshund indicators - use more precise regex pattern
                // We're looking for entire words, not substrings
                if (preg_match('/\bavlshund\b/i', $value) || preg_match('/\bavlsh\b/i', $value)) {
                    // Try to determine which parent this refers to
                    $is_relevant = false;
                    
                    // Check if field contains parent reference
                    foreach ($status_prefix as $prefix) {
                        if (strpos($value, $prefix) !== false) {
                            $is_relevant = true;
                            break;
                        }
                    }
                    
                    // Check if field is directly related to this parent - be more strict
                    if ($field === 'FatherPrem' && $parent == 'father') $is_relevant = true;
                    if ($field === 'MotherPrem' && $parent == 'mother') $is_relevant = true;
                    if ($field === 'althdF' && $parent == 'father') $is_relevant = true;
                    if ($field === 'althdM' && $parent == 'mother') $is_relevant = true;
                    
                    if ($is_relevant) {
                        $result['avlshund'] = true;
                        $result['avlshund_reason'] = 'felt ' . $field . ' har "avlshund"';
                    }
                }
                
                // Check for elitehund indicators
                if (strpos($value, 'elite') !== false) {
                    // Try to determine which parent this refers to
                    $is_relevant = false;
                    
                    // Check if field contains parent reference
                    foreach ($status_prefix as $prefix) {
                        if (strpos($value, $prefix) !== false) {
                            $is_relevant = true;
                            break;
                        }
                    }
                    
                    // Check if field is directly related to this parent
                    if ($field === 'FatherPrem' && $parent == 'father') $is_relevant = true;
                    if ($field === 'MotherPrem' && $parent == 'mother') $is_relevant = true;
                    if ($field === 'althdF' && $parent == 'father') $is_relevant = true;
                    if ($field === 'althdM' && $parent == 'mother') $is_relevant = true;
                    
                    if ($is_relevant) {
                        $result['elitehund'] = true;
                        $result['elitehund_reason'] = 'felt ' . $field . ' har "elite"';
                    }
                }
            }
        }
    }
    
    // 5. Check jakt scores for elite status
    if (!empty($criteria_config['use_jakt_scores'])) {
        $jakt_threshold = !empty($criteria_config['jakt_threshold']) ? intval($criteria_config['jakt_threshold']) : 115;
        
        if (isset($valp[$jakt_field]) && is_numeric($valp[$jakt_field]) && intval($valp[$jakt_field]) > $jakt_threshold) {
            $result['elitehund'] = true;
            $result['elitehund_reason'] = $jakt_field . '=' . $valp[$jakt_field];
        }
    }
    
    return $result;
} // End function get_dog_status
} // End if !function_exists