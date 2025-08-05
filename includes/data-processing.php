<?php
/* filepath: /Users/erlendo/Local Sites/pointerdatabasen/app/public/wp-content/plugins/NPK_Valpeliste/includes/data-processing.php */
/**
 * Data processing functions for NPK Valpeliste plugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Authenticate to datahound.no and get session cookies
 * @param bool $debug_mode Enable debugging output
 * @return array|false Array of cookies on success, false on failure
 */
if (!function_exists('authenticate_datahound')) {
function authenticate_datahound($debug_mode = false) {
    $login_url = 'https://pointer.datahound.no/admin';
    $login_action_url = 'https://pointer.datahound.no/admin/index/auth';
    $username = 'demo';
    $password = 'demo';
    
    $debug_info = '';
    if ($debug_mode) {
        $debug_info .= '<div style="background:#fff3cd;border:1px solid #ffeaa7;padding:10px;margin:10px 0;">';
        $debug_info .= '<h5>🔐 Autentiserer til datahound.no</h5>';
        $debug_info .= '<p><strong>Brukernavn:</strong> ' . esc_html($username) . '</p>';
        $debug_info .= '<p><strong>Login URL:</strong> ' . esc_html($login_url) . '</p>';
        $debug_info .= '<p><strong>Action URL:</strong> ' . esc_html($login_action_url) . '</p>';
    }
    
    // First, get the login page to get any CSRF tokens or session cookies
    $response = wp_remote_get($login_url, array(
        'timeout' => 30,
        'user-agent' => 'NPK Valpeliste Plugin v' . (defined('NPK_VALPELISTE_VERSION') ? NPK_VALPELISTE_VERSION : '1.5'),
        'headers' => array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        )
    ));
    
    if (is_wp_error($response)) {
        if ($debug_mode) {
            $debug_info .= '<p style="color:#cc0000;">❌ Feil ved henting av login-side: ' . esc_html($response->get_error_message()) . '</p></div>';
            return $debug_info;
        }
        return false;
    }
    
    $login_body = wp_remote_retrieve_body($response);
    $login_cookies = wp_remote_retrieve_cookies($response);
    
    // Extract CSRF token if present
    $csrf_token = '';
    if (preg_match('/<input[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']*)["\']/', $login_body, $matches)) {
        $csrf_token = $matches[1];
    } elseif (preg_match('/<meta[^>]*name=["\']csrf-token["\'][^>]*content=["\']([^"\']*)["\']/', $login_body, $matches)) {
        $csrf_token = $matches[1];
    }
    
    if ($debug_mode) {
        $debug_info .= '<p>📄 Login-side hentet (' . strlen($login_body) . ' bytes)</p>';
        $debug_info .= '<p>🍪 Initial cookies: ' . count($login_cookies) . '</p>';
        if ($csrf_token) {
            $debug_info .= '<p>🔑 CSRF token funnet</p>';
        }
    }
    
    // Prepare login data with correct field names
    $login_data = array(
        'admin_username' => $username,
        'admin_password' => $password,
        'login' => 'login',
    );
    
    if ($csrf_token) {
        $login_data['_token'] = $csrf_token;
        $login_data['csrf_token'] = $csrf_token;
    }
    
    // Attempt login to the correct action URL
    $login_response = wp_remote_post($login_action_url, array(
        'timeout' => 30,
        'user-agent' => 'NPK Valpeliste Plugin v' . (defined('NPK_VALPELISTE_VERSION') ? NPK_VALPELISTE_VERSION : '1.4'),
        'cookies' => $login_cookies,
        'headers' => array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Referer' => $login_url,
            'Origin' => 'https://pointer.datahound.no',
        ),
        'body' => $login_data
    ));
    
    if (is_wp_error($login_response)) {
        if ($debug_mode) {
            $debug_info .= '<p style="color:#cc0000;">❌ Login-feil: ' . esc_html($login_response->get_error_message()) . '</p></div>';
            return $debug_info;
        }
        return false;
    }
    
    $response_code = wp_remote_retrieve_response_code($login_response);
    $response_cookies = wp_remote_retrieve_cookies($login_response);
    $response_headers = wp_remote_retrieve_headers($login_response);
    $response_body = wp_remote_retrieve_body($login_response);
    
    if ($debug_mode) {
        $debug_info .= '<p>📡 Login-respons: HTTP ' . esc_html($response_code) . '</p>';
        $debug_info .= '<p>🍪 Response cookies: ' . count($response_cookies) . '</p>';
        if (isset($response_headers['location'])) {
            $debug_info .= '<p>📍 Redirect til: ' . esc_html($response_headers['location']) . '</p>';
        }
    }
    
    // Check for successful login (redirect or specific response)
    $login_successful = false;
    if ($response_code === 302 || $response_code === 301) {
        // Redirect usually indicates successful login
        $login_successful = true;
    } elseif ($response_code === 200) {
        // Check if we're not on the login page anymore
        if (strpos($response_body, 'dashboard') !== false || 
            strpos($response_body, 'admin') !== false ||
            strpos($response_body, 'logout') !== false) {
            $login_successful = true;
        }
    }
    
    if ($login_successful && !empty($response_cookies)) {
        if ($debug_mode) {
            $debug_info .= '<p style="color:#009900;">✅ Login vellykket!</p>';
            $debug_info .= '<p>🎫 Session cookies mottatt</p></div>';
        }
        
        // Convert WP cookies to our format
        $auth_cookies = array();
        foreach ($response_cookies as $cookie) {
            $auth_cookies[] = new WP_Http_Cookie(array(
                'name' => $cookie->name,
                'value' => $cookie->value,
                'domain' => '.datahound.no'
            ));
        }
        
        // Also include initial cookies
        foreach ($login_cookies as $cookie) {
            $auth_cookies[] = new WP_Http_Cookie(array(
                'name' => $cookie->name,
                'value' => $cookie->value,
                'domain' => '.datahound.no'
            ));
        }
        
        if ($debug_mode) {
            return array('debug' => $debug_info, 'cookies' => $auth_cookies);
        }
        return $auth_cookies;
    }
    
    if ($debug_mode) {
        $debug_info .= '<p style="color:#cc0000;">❌ Login mislyktes</p>';
        $debug_info .= '<p>Sjekk brukernavn/passord eller login-prosedyre</p></div>';
        return $debug_info;
    }
    
    return false;
}
}

/**
 * Get authentication cookies for datahound.no API
 * @param bool $debug_mode Enable debugging output  
 * @return array Array of WP_Http_Cookie objects
 */
if (!function_exists('get_datahound_auth_cookies')) {
function get_datahound_auth_cookies($debug_mode = false) {
    // First try to authenticate and get fresh cookies
    $auth_result = authenticate_datahound($debug_mode);
    
    if ($debug_mode && is_array($auth_result) && isset($auth_result['cookies'])) {
        return $auth_result; // Return debug info and cookies
    } elseif (is_array($auth_result) && !isset($auth_result['debug'])) {
        return $auth_result; // Return just cookies
    }
    
    // Fallback: check for existing cookies from the browser session
    $cookies = array();
    if (!empty($_COOKIE)) {
        foreach ($_COOKIE as $name => $value) {
            // Look for session-related cookies that might be needed for authentication
            if (
                strpos($name, 'PHPSESSID') !== false ||
                strpos($name, 'session') !== false ||
                strpos($name, 'datahound') !== false ||
                strpos($name, 'pointer') !== false ||
                strpos($name, 'auth') !== false ||
                strpos($name, 'login') !== false ||
                strpos($name, 'remember') !== false
            ) {
                $cookies[] = new WP_Http_Cookie(array(
                    'name' => $name, 
                    'value' => $value,
                    'domain' => '.datahound.no'
                ));
            }
        }
    }
    
    return $cookies;
}
}

/**
 * Fetch puppy data from the external API - LIVE DATA ONLY (NO CACHE)
 * @param bool $force_refresh Unused parameter (kept for compatibility)
 * @param bool $debug_mode Enable debugging output
 * @return array|string Data from API or error message
 */
if (!function_exists('fetch_puppy_data')) {
function fetch_puppy_data($force_refresh = false, $debug_mode = false) {
    // NO CACHING - Always fetch fresh data from API
    
    // Correct API endpoint for datahound.no
    $api_url = 'https://pointer.datahound.no/admin/product/getvalpeliste';
    
    $debug_info = '';
    if ($debug_mode) {
        $debug_info = '<div style="background:#f0f8ff;border:1px solid #0066cc;padding:15px;margin:15px 0;">';
        $debug_info .= '<h3>🌐 Live API Call to Datahound.no</h3>';
        $debug_info .= '<p><strong>Mode:</strong> ALWAYS LIVE DATA - NO CACHING</p>';
        $debug_info .= '<p><strong>Data freshness:</strong> Real-time from datahound.no</p>';
        $debug_info .= '<hr>';
    }
    
    $successful_data = null;
    $last_error = '';
    
    if ($debug_mode) {
        $debug_info .= '<p><strong>🔍 API Endpoint:</strong> ' . esc_html($api_url) . '</p>';
        $debug_info .= '<p><strong>🔐 Authentication:</strong> Session cookies (admin login required)</p>';
    }
    
    // Make the authenticated API request
    $start_time = microtime(true);
    
    // Get authentication cookies with debug info if needed
    $auth_result = get_datahound_auth_cookies($debug_mode);
    $cookies = array();
    $auth_debug = '';
    
    if ($debug_mode && is_array($auth_result) && isset($auth_result['debug'])) {
        $auth_debug = $auth_result['debug'];
        $cookies = isset($auth_result['cookies']) ? $auth_result['cookies'] : array();
        $debug_info .= $auth_debug;
    } elseif (is_array($auth_result)) {
        $cookies = $auth_result;
    }
    
    if ($debug_mode) {
        $debug_info .= '<p><strong>🍪 Auth Cookies:</strong> ' . count($cookies) . ' cookies ready</p>';
    }
    
    $response = wp_remote_get($api_url, array(
        'timeout' => 30,
        'redirection' => 5,
        'httpversion' => '1.1',
        'user-agent' => 'NPK Valpeliste Plugin v' . (defined('NPK_VALPELISTE_VERSION') ? NPK_VALPELISTE_VERSION : '1.3') . ' (WordPress/' . get_bloginfo('version') . ')',
        'cookies' => $cookies,
        'headers' => array(
            'Accept' => 'application/json, text/html, */*',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Referer' => 'https://pointer.datahound.no/',
            'X-Requested-With' => 'XMLHttpRequest',
            'Origin' => 'https://pointer.datahound.no'
        )
    ));
    $end_time = microtime(true);
    $request_time = round(($end_time - $start_time) * 1000, 2);
    
    // Check for WordPress HTTP errors
    if (is_wp_error($response)) {
        $error_msg = $response->get_error_message();
        $last_error = $error_msg;
        if ($debug_mode) {
            $debug_info .= '<span style="color:#cc0000;">   ❌ WP Error (' . $request_time . 'ms): ' . esc_html($error_msg) . '</span><br>';
        }
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $headers = wp_remote_retrieve_headers($response);
        
        if ($debug_mode) {
            $debug_info .= '<span style="color:' . ($response_code === 200 ? '#009900' : '#ff6600') . ';">   📡 HTTP ' . esc_html($response_code) . ' (' . $request_time . 'ms)</span><br>';
            $debug_info .= '   📏 Response Size: ' . strlen($body) . ' bytes<br>';
            if (isset($headers['content-type'])) {
                $debug_info .= '   📋 Content-Type: ' . esc_html($headers['content-type']) . '<br>';
            }
            $debug_info .= '   🍪 Cookies Sent: ' . count($cookies) . '<br>';
        }
        
        // Handle different response codes
        if ($response_code === 200) {
            // Check if body is empty
            if (empty($body)) {
                $last_error = 'Empty response body';
                if ($debug_mode) {
                    $debug_info .= '<span style="color:#cc0000;">   ❌ Empty response</span><br>';
                }
            } else {
                // Try to parse as JSON
                $json_data = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json_data) && !empty($json_data)) {
                    $successful_data = $json_data;
                    if ($debug_mode) {
                        $debug_info .= '<span style="color:#009900;">   ✅ SUCCESS: Valid JSON data!</span><br>';
                        // Count dogs if available, otherwise count total records
                        $record_count = isset($json_data['dogs']) && is_array($json_data['dogs']) ? count($json_data['dogs']) : count($json_data);
                        $debug_info .= '   📊 Records Found: ' . $record_count . '<br>';
                        if (isset($json_data['totalCount'])) {
                            $debug_info .= '   📈 Total Count (API): ' . $json_data['totalCount'] . '<br>';
                        }
                        $debug_info .= '   🎯 Data Source: ' . esc_html($api_url) . '<br>';
                    }
                } else {
                    // Check if response looks like HTML (login page or error page)
                    if (stripos($body, '<html') !== false || stripos($body, '<!doctype') !== false) {
                        $last_error = 'Received HTML page - likely need admin login to datahound.no';
                        if ($debug_mode) {
                            $debug_info .= '<span style="color:#ff6600;">   🌐 HTML page returned - authentication required</span><br>';
                            $debug_info .= '   📄 Content Preview: ' . esc_html(substr(strip_tags($body), 0, 200)) . '...<br>';
                        }
                    } else {
                        // JSON parsing failed
                        $last_error = 'Invalid JSON: ' . json_last_error_msg();
                        if ($debug_mode) {
                            $debug_info .= '<span style="color:#cc0000;">   ❌ JSON Error: ' . esc_html(json_last_error_msg()) . '</span><br>';
                            $debug_info .= '   📄 Content Preview: ' . esc_html(substr($body, 0, 200)) . '...<br>';
                        }
                    }
                }
            }
        } elseif ($response_code === 401 || $response_code === 403) {
            $last_error = 'Authentication required - must be logged in to datahound.no admin';
            if ($debug_mode) {
                $debug_info .= '<span style="color:#ff6600;">   🔐 Authentication required</span><br>';
            }
        } else {
            $last_error = 'HTTP ' . $response_code . ' error';
        }
    }
    
    // Final result handling
    if ($debug_mode) {
        $debug_info .= '<hr>';
    }
    
    if ($successful_data === null) {
        // NO DATA FOUND - NO FALLBACK
        $final_error = '🚫 INGEN DATA FUNNET fra datahound.no';
        $detailed_error = 'API URL: ' . $api_url . ' - Feil: ' . $last_error;
        
        error_log('NPK Valpeliste LIVE MODE: ' . $final_error . ' - ' . $detailed_error);
        
        if ($debug_mode) {
            $debug_info .= '<div style="background:#ffe6e6;border:2px solid #cc0000;padding:15px;margin:10px 0;">';
            $debug_info .= '<h4 style="color:#cc0000;margin:0 0 10px 0;">❌ INGEN DATA TILGJENGELIG</h4>';
            $debug_info .= '<p><strong>Feil:</strong> ' . esc_html($detailed_error) . '</p>';
            $debug_info .= '<p><strong>API Endpoint:</strong> ' . esc_html($api_url) . '</p>';
            if (strpos($last_error, 'authentication') !== false || strpos($last_error, 'login') !== false) {
                $debug_info .= '<div style="background:#fff3cd;border:1px solid #ffeaa7;padding:10px;margin:10px 0;">';
                $debug_info .= '<h5>🔐 Autentisering Påkrevd</h5>';
                $debug_info .= '<p>Dette API-endepunktet krever innlogging til datahound.no admin-panelet.</p>';
                $debug_info .= '<p><strong>Løsning:</strong> Logg inn på <a href="https://pointer.datahound.no/admin" target="_blank">pointer.datahound.no/admin</a> først, deretter prøv igjen.</p>';
                $debug_info .= '</div>';
            }
            $debug_info .= '<p><em>LIVE MODE: Ingen fallback-data brukes.</em></p>';
            $debug_info .= '</div></div>';
            
            return $debug_info;
        }
        
        // Return empty array - absolutely NO fallback data
        return array();
    }
    
    // SUCCESS - NO CACHING, always return fresh data
    
    if ($debug_mode) {
        $success_info = '<div style="background:#e6ffe6;border:2px solid #009900;padding:15px;margin:10px 0;">';
        $success_info .= '<h4 style="color:#009900;margin:0 0 10px 0;">✅ FERSKE DATA HENTET</h4>';
        $success_info .= '<p><strong>Kilde:</strong> Live API fra datahound.no (INGEN CACHE)</p>';
        // Count dogs if available, otherwise count total records  
        $record_count = isset($successful_data['dogs']) && is_array($successful_data['dogs']) ? count($successful_data['dogs']) : count($successful_data);
        $success_info .= '<p><strong>Antall poster:</strong> ' . $record_count . '</p>';
        if (isset($successful_data['totalCount'])) {
            $success_info .= '<p><strong>Total Count (API):</strong> ' . $successful_data['totalCount'] . '</p>';
        }
        $success_info .= '<p><strong>Data alder:</strong> Real-time (hentet akkurat nå)</p>';
        $success_info .= '</div></div>';
        
        return array(
            'debug' => $debug_info . $success_info,
            'data' => $successful_data
        );
    }
    
    return $successful_data;
} // End function fetch_puppy_data
} // End if !function_exists

/**
 * Process puppy data into approved and other categories
 * @param array $data Raw puppy data
 * @return array Processed data
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
} // End function process_puppy_data
} // End if !function_exists

/**
 * Extract parent data
 * @param array|object $valp Puppy data
 * @return array Parent information
 */
if (!function_exists('extract_parent_data')) {
function extract_parent_data($valp) {
    return array(
        'father_name' => get_safe_value($valp, ['FatherName', 'father_name', 'far_name'], 'N/A'),
        'father_reg' => get_safe_value($valp, ['father', 'far', 'FatherReg'], ''),
        'mother_name' => get_safe_value($valp, ['MotherName', 'mother_name', 'mor_name'], 'N/A'),
        'mother_reg' => get_safe_value($valp, ['mother', 'mor', 'MotherReg'], '')
    );
} // End function extract_parent_data
} // End if !function_exists

/**
 * Konverterer valpeliste til ny struktur med ALLE individuelle data for far og mor
 * @param array $dogs Array av kull fra datahound
 * @return array Ny strukturert array
 */
function convert_to_individual_structure($dogs) {
    // Safety check: ensure we have an array
    if (!is_array($dogs)) {
        error_log('NPK Valpeliste: convert_to_individual_structure() called with non-array: ' . gettype($dogs));
        return [];
    }
    
    // Safety check: ensure we have at least one item
    if (empty($dogs)) {
        error_log('NPK Valpeliste: convert_to_individual_structure() called with empty array');
        return [];
    }
    
    $new_dogs = [];
    foreach ($dogs as $index => $dog) {
        // Safety check: ensure each dog is an array
        if (!is_array($dog)) {
            error_log('NPK Valpeliste: Individual dog data at index ' . $index . ' is not an array: ' . gettype($dog));
            continue;
        }
        
        // Safety check: ensure dog has required data
        if (empty($dog)) {
            error_log('NPK Valpeliste: Individual dog data at index ' . $index . ' is empty');
            continue;
        }
        
        $father = [];
        $mother = [];
        $dog_clean = is_array($dog) ? $dog : [];
        
        // Only process if dog_clean is actually an array
        if (!is_array($dog_clean)) {
            error_log('NPK Valpeliste: dog_clean is not an array at index ' . $index);
            continue;
        }
        
        foreach ($dog as $key => $value) {
            // Spesiell håndtering for avlsh og eliteh - disse gjelder hele kullet, 
            // så vi må bestemme hvordan de skal fordeles på far/mor
            if ($key == 'avlsh' || $key == 'eliteh') {
                // Hvis kullet har eliteh=1, betyr det at minst en forelder er elitehund
                // Vi bruker en heuristikk basert på premie-score for å bestemme hvem
                if ($key == 'eliteh' && $value == '1') {
                    $father_premie = isset($dog['premie']) ? (int)$dog['premie'] : 0;
                    $mother_premie = isset($dog['PremieM']) ? (int)$dog['PremieM'] : 0;
                    
                    // Hvis begge har høye premie-score, gi begge eliteh status
                    if ($father_premie >= 10 && $mother_premie >= 10) {
                        $father['eliteh'] = $value;
                        $mother['eliteh'] = $value;
                    }
                    // Ellers gi til den med høyest premie-score
                    elseif ($father_premie > $mother_premie) {
                        $father['eliteh'] = $value;
                        $mother['eliteh'] = '0';
                    } else {
                        $mother['eliteh'] = $value;
                        $father['eliteh'] = '0';
                    }
                } elseif ($key == 'avlsh') {
                    // Avlsh fordeles likt til begge hvis aktivt
                    $father['avlsh'] = $value;
                    $mother['avlsh'] = $value;
                }
                continue; // La disse forbli i det originale objektet også
            }
            
            // Prefiks: Father/father
            if (preg_match('/^(Father|father)/', $key)) {
                $father[$key] = $value;
                if (is_array($dog_clean)) unset($dog_clean[$key]);
            }
            // Prefiks: Mother/mother
            else if (preg_match('/^(Mother|mother)/', $key)) {
                $mother[$key] = $value;
                if (is_array($dog_clean)) unset($dog_clean[$key]);
            }
            // Postfiks: F (far) eller M (mor) - men ikke for korte navn
            else if (preg_match('/[a-zA-Z]{2,}F$/', $key)) {
                $father[$key] = $value;
                if (is_array($dog_clean)) unset($dog_clean[$key]);
            }
            else if (preg_match('/[a-zA-Z]{2,}M$/', $key)) {
                $mother[$key] = $value;
                if (is_array($dog_clean)) unset($dog_clean[$key]);
            }
            // Spesialfelter som kan være individuelle (men ikke avlsh/eliteh som er spesifikke)
            else if (in_array($key, [
                'fatherHD', 'motherHD', 'FatherPrem', 'MotherPrem', 'althdFather', 'althdMother', 'adrF', 'adrM', 'fatherOwner', 'motherOwner', 'died', 'dwid', 'fikstmt', 'lastUpdated', 'edit', 'site', 'stdjktind', 'eh', 'regby', 'RAID', 'PremieM', 'jaktM', 'jaktindM', 'standindM', 'althdM', 'PremieF', 'jaktF', 'jaktindF', 'standindF', 'althdF'])) {
                // Prøv å fordele på far/mor hvis mulig
                if (preg_match('/(F|father)/i', $key)) {
                    $father[$key] = $value;
                    if (is_array($dog_clean)) unset($dog_clean[$key]);
                } else if (preg_match('/(M|mother)/i', $key)) {
                    $mother[$key] = $value;
                    if (is_array($dog_clean)) unset($dog_clean[$key]);
                } else {
                    // Hvis ikke, legg på begge
                    $father[$key] = $value;
                    $mother[$key] = $value;
                    if (is_array($dog_clean)) unset($dog_clean[$key]);
                }
            }
        }
        $dog_clean['father'] = $father;
        $dog_clean['mother'] = $mother;
        $new_dogs[] = $dog_clean;
    }
    return $new_dogs;
}