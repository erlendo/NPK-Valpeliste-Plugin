<?php
/**
 * Enhanced test to show all current dogs and look for Wild Desert Storm
 */

// Test direct authentication to datahound.no
echo "=== Enhanced NPK Valpeliste Test - Searching for Wild Desert Storm ===\n\n";

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

echo "1. Getting login page...\n";
$login_page = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "   Status: HTTP $status\n";

if ($status == 200) {
    // Look for CSRF token
    $csrf_token = '';
    if (preg_match('/<input[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']*)["\']/', $login_page, $matches)) {
        $csrf_token = $matches[1];
        echo "   CSRF token found\n";
    } else {
        echo "   No CSRF token found\n";
    }

    // Perform login
    $login_data = http_build_query([
        'admin_username' => $username,
        'admin_password' => $password,
        'login' => 'login',
        '_token' => $csrf_token,
        'csrf_token' => $csrf_token
    ]);
    
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
    
    echo "\n2. Attempting login...\n";
    $login_response = curl_exec($ch);
    $login_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check if login was successful
    if (stripos($login_response, 'admin') !== false && stripos($login_response, 'dashboard') !== false) {
        echo "   ✅ Login successful\n";
        
        // Now get the API data
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://pointer.datahound.no/admin/product/getvalpeliste',
            CURLOPT_POST => false,
            CURLOPT_POSTFIELDS => null
        ]);
        
        echo "\n3. Getting API data...\n";
        $api_response = curl_exec($ch);
        $api_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($api_status == 200) {
            $data = json_decode($api_response, true);
            
            if ($data && isset($data['dogs'])) {
                echo "   ✅ Found " . count($data['dogs']) . " dogs\n\n";
                
                $found_wild_desert = false;
                
                // Show all dogs and search for Wild Desert Storm
                foreach ($data['dogs'] as $index => $dog) {
                    echo "=== DOG {$index} ===\n";
                    echo "KUID: " . ($dog['KUID'] ?? 'N/A') . "\n";
                    echo "Kennel: " . ($dog['kennel'] ?? 'N/A') . "\n";
                    echo "Owner: " . ($dog['owner'] ?? 'N/A') . "\n";
                    echo "Father: " . ($dog['FatherName'] ?? 'N/A') . "\n";
                    echo "Father Reg: " . ($dog['FatherReg'] ?? 'N/A') . "\n";
                    echo "Mother: " . ($dog['MotherName'] ?? 'N/A') . "\n";
                    echo "Mother Reg: " . ($dog['MotherReg'] ?? 'N/A') . "\n";
                    
                    // Badge fields
                    echo "Father avlsh: '" . ($dog['Fatheravlsh'] ?? '') . "'\n";
                    echo "Father eliteh: '" . ($dog['Fathereliteh'] ?? '') . "'\n";
                    echo "Mother avlsh: '" . ($dog['Motheravlsh'] ?? '') . "'\n";
                    echo "Mother eliteh: '" . ($dog['Mothereliteh'] ?? '') . "'\n";
                    
                    // Check if this involves Wild Desert Storm
                    $search_fields = [
                        'FatherName', 'FatherReg', 'MotherName', 'MotherReg'
                    ];
                    
                    foreach ($search_fields as $field) {
                        if (isset($dog[$field])) {
                            if (stripos($dog[$field], 'Wild Desert Storm') !== false ||
                                stripos($dog[$field], 'NO46865/21') !== false ||
                                stripos($dog[$field], 'Huldreveien') !== false) {
                                echo "*** FOUND WILD DESERT STORM in {$field}! ***\n";
                                $found_wild_desert = true;
                            }
                        }
                    }
                    
                    echo "\n";
                }
                
                if (!$found_wild_desert) {
                    echo "=== CONCLUSION ===\n";
                    echo "❌ Wild Desert Storm (NO46865/21) is NOT found in the current API data.\n";
                    echo "This means either:\n";
                    echo "1. The dog is not currently part of any active litters in the database\n";
                    echo "2. The litter registration has been updated/removed\n";
                    echo "3. The dog name or registration number has changed\n\n";
                    echo "The current 5 dogs in the system are shown above.\n";
                } else {
                    echo "=== FOUND WILD DESERT STORM ===\n";
                    echo "✅ Wild Desert Storm is present in the current data.\n";
                }
                
            } else {
                echo "   ❌ Invalid JSON response\n";
            }
        } else {
            echo "   ❌ API call failed with status: $api_status\n";
        }
    } else {
        echo "   ❌ Login failed\n";
    }
} else {
    echo "   ❌ Login page access failed\n";
}

curl_close($ch);
echo "\n=== Test Complete ===\n";
?>
