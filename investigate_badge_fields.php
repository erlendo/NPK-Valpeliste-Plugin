<?php
/**
 * Raw API data investigation for badge fields
 */

echo "=== Raw API Data Investigation for Badge Fields ===\n\n";

$login_url = 'https://pointer.datahound.no/admin';
$login_action_url = 'https://pointer.datahound.no/admin/index/auth';
$username = 'demo';
$password = 'demo';

// Initialize cURL
$ch = curl_init();

// Get login page first
curl_setopt_array($ch, [
    CURLOPT_URL => $login_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR => '/tmp/cookies.txt',
    CURLOPT_COOKIEFILE => '/tmp/cookies.txt',
    CURLOPT_USERAGENT => 'NPK Valpeliste Test',
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_VERBOSE => false
]);

$login_page = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code == 200) {
    // Look for CSRF token
    $csrf_token = '';
    if (preg_match('/<input[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']*)["\']/', $login_page, $matches)) {
        $csrf_token = $matches[1];
    }

    // Prepare login data
    $login_data = http_build_query([
        'admin_username' => $username,
        'admin_password' => $password,
        'login' => 'login',
        '_token' => $csrf_token,
        'csrf_token' => $csrf_token
    ]);

    // Perform login
    curl_setopt_array($ch, [
        CURLOPT_URL => $login_action_url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $login_data,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: ' . $login_url,
            'Origin: https://pointer.datahound.no'
        ]
    ]);

    $login_response = curl_exec($ch);
    $login_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check if login was successful
    $login_successful = false;
    if ($login_http_code == 302 || $login_http_code == 301) {
        $login_successful = true;
    } elseif (strpos($login_response, 'dashboard') !== false || 
              strpos($login_response, 'admin') !== false ||
              strpos($login_response, 'logout') !== false) {
        $login_successful = true;
    }

    if ($login_successful) {
        echo "✅ Authentication successful\n\n";
        
        // Get API data
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://pointer.datahound.no/admin/product/getvalpeliste',
            CURLOPT_POST => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Referer: https://pointer.datahound.no/admin/'
            ]
        ]);
        
        $api_response = curl_exec($ch);
        $api_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($api_http_code == 200) {
            $data = json_decode($api_response, true);
            
            if ($data && isset($data['dogs'])) {
                // Find Wild Desert Storm and show ALL raw fields
                foreach ($data['dogs'] as $index => $dog) {
                    if (isset($dog['FatherName']) && stripos($dog['FatherName'], 'Wild Desert Storm') !== false) {
                        echo "=== FOUND Wild Desert Storm (Dog {$index}) - RAW DATA ===\n";
                        echo "KUID: " . ($dog['KUID'] ?? 'N/A') . "\n";
                        echo "Kennel: " . ($dog['kennel'] ?? 'N/A') . "\n";
                        echo "Father: " . ($dog['FatherName'] ?? 'N/A') . "\n";
                        echo "Mother: " . ($dog['MotherName'] ?? 'N/A') . "\n\n";
                        
                        echo "ALL AVAILABLE FIELDS:\n";
                        foreach ($dog as $field => $value) {
                            if (is_string($value)) {
                                echo "{$field}: '{$value}'\n";
                            } else {
                                echo "{$field}: " . json_encode($value) . "\n";
                            }
                        }
                        echo "\n";
                        
                        // Look specifically for badge-related fields
                        echo "BADGE-RELATED FIELDS:\n";
                        $badge_fields = [];
                        foreach ($dog as $field => $value) {
                            if (stripos($field, 'elite') !== false || 
                                stripos($field, 'avls') !== false || 
                                stripos($field, 'badge') !== false ||
                                stripos($field, 'prem') !== false ||
                                stripos($field, 'father') !== false ||
                                stripos($field, 'mother') !== false) {
                                $badge_fields[$field] = $value;
                                echo "  {$field}: '{$value}'\n";
                            }
                        }
                        
                        if (empty($badge_fields)) {
                            echo "  No obvious badge fields found!\n";
                        }
                        
                        break;
                    }
                }
                
                // Also check if there are any fields with 'eliteh' or 'avlsh' in ANY dog
                echo "\n=== CHECKING ALL DOGS FOR ELITEH/AVLSH FIELDS ===\n";
                $all_fields = [];
                foreach ($data['dogs'] as $dog) {
                    foreach ($dog as $field => $value) {
                        $all_fields[$field] = true;
                    }
                }
                
                echo "All unique field names in API response:\n";
                $eliteh_fields = [];
                $avlsh_fields = [];
                foreach (array_keys($all_fields) as $field) {
                    if (stripos($field, 'elite') !== false) {
                        $eliteh_fields[] = $field;
                    }
                    if (stripos($field, 'avls') !== false) {
                        $avlsh_fields[] = $field;
                    }
                    echo "  - {$field}\n";
                }
                
                echo "\nEliteh-related fields: " . implode(', ', $eliteh_fields) . "\n";
                echo "Avlsh-related fields: " . implode(', ', $avlsh_fields) . "\n";
                
            } else {
                echo "❌ No dogs data in API response\n";
            }
        } else {
            echo "❌ API call failed with status: {$api_http_code}\n";
        }
    } else {
        echo "❌ Authentication failed\n";
    }
} else {
    echo "❌ Login page access failed\n";
}

curl_close($ch);
echo "\n=== Investigation Complete ===\n";
?>
