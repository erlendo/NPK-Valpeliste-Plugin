<?php
/**
 * Detailed search for Wild Desert Storm using working authentication
 */

echo "=== Detailed Wild Desert Storm Search ===\n\n";

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
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($login_page === false) {
    echo "ERROR: " . curl_error($ch) . "\n";
    exit(1);
}

echo "   Status: HTTP $http_code\n";

// Look for CSRF token
$csrf_token = '';
if (preg_match('/<input[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']*)["\']/', $login_page, $matches)) {
    $csrf_token = $matches[1];
    echo "   CSRF token found\n";
} else {
    echo "   No CSRF token found\n";
}

// Prepare login data with correct field names
$login_data = http_build_query([
    'admin_username' => $username,
    'admin_password' => $password,
    'login' => 'login',
    '_token' => $csrf_token,
    'csrf_token' => $csrf_token
]);

echo "\n2. Attempting login...\n";

// Perform login to the correct action URL
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
$redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

echo "   Login response: HTTP $login_http_code\n";

// Check if login was successful
$login_successful = false;
if ($login_http_code == 302 || $login_http_code == 301) {
    $login_successful = true;
    echo "   ✅ Login successful (redirect response)\n";
} elseif (strpos($login_response, 'dashboard') !== false || 
          strpos($login_response, 'admin') !== false ||
          strpos($login_response, 'logout') !== false) {
    $login_successful = true;
    echo "   ✅ Login successful (admin content detected)\n";
} else {
    echo "   ❌ Login failed\n";
}

// Test API endpoint
if ($login_successful) {
    echo "\n3. Getting detailed dog data...\n";
    
    $api_url = 'https://pointer.datahound.no/admin/product/getvalpeliste';
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_POST => false,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Referer: https://pointer.datahound.no/admin/'
        ]
    ]);
    
    $api_response = curl_exec($ch);
    $api_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "   API response: HTTP $api_http_code\n";
    
    if ($api_http_code == 200) {
        $data = json_decode($api_response, true);
        if ($data !== null && isset($data['dogs'])) {
            echo "   ✅ Found " . count($data['dogs']) . " dogs\n\n";
            
            $found_wild_desert = false;
            
            foreach ($data['dogs'] as $index => $dog) {
                echo "=== DOG {$index} ===\n";
                echo "KUID: " . ($dog['KUID'] ?? 'N/A') . "\n";
                echo "Kennel: " . ($dog['kennel'] ?? 'N/A') . "\n";
                echo "Owner: " . ($dog['owner'] ?? 'N/A') . "\n";
                echo "Father: " . ($dog['FatherName'] ?? 'N/A') . "\n";
                echo "Father Reg: " . ($dog['FatherReg'] ?? 'N/A') . "\n";
                echo "Mother: " . ($dog['MotherName'] ?? 'N/A') . "\n";
                echo "Mother Reg: " . ($dog['MotherReg'] ?? 'N/A') . "\n";
                
                // Show all badge-related fields
                echo "Father avlsh: '" . ($dog['Fatheravlsh'] ?? '') . "'\n";
                echo "Father eliteh: '" . ($dog['Fathereliteh'] ?? '') . "'\n";
                echo "Mother avlsh: '" . ($dog['Motheravlsh'] ?? '') . "'\n";
                echo "Mother eliteh: '" . ($dog['Mothereliteh'] ?? '') . "'\n";
                
                // Check if this involves Wild Desert Storm
                $search_fields = ['FatherName', 'FatherReg', 'MotherName', 'MotherReg'];
                $search_terms = ['Wild Desert Storm', 'NO46865/21', 'Huldreveien'];
                
                foreach ($search_fields as $field) {
                    if (isset($dog[$field])) {
                        foreach ($search_terms as $term) {
                            if (stripos($dog[$field], $term) !== false) {
                                echo "*** FOUND '{$term}' in {$field}! ***\n";
                                $found_wild_desert = true;
                            }
                        }
                    }
                }
                
                echo "\n";
            }
            
            echo "=== SEARCH RESULT ===\n";
            if (!$found_wild_desert) {
                echo "❌ Wild Desert Storm (NO46865/21) is NOT currently found in the API data.\n";
                echo "This means the dog is not part of any active litters in the current dataset.\n";
                echo "The valpeliste (puppy list) only shows dogs that are actively breeding or have recent litters.\n\n";
                echo "Possible reasons:\n";
                echo "- The litter has been completed and removed from active listings\n";
                echo "- The dog is not currently involved in any new breeding programs\n";
                echo "- The registration information has been updated\n";
                echo "- The dog data is in a different section of the database\n";
            } else {
                echo "✅ Wild Desert Storm found in current active litter data!\n";
            }
            
        } else {
            echo "   ❌ Invalid JSON response or no dogs data\n";
        }
    } else {
        echo "   ❌ API call failed\n";
    }
}

curl_close($ch);
echo "\n=== Search Complete ===\n";
?>
