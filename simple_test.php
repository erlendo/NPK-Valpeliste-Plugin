<?php
/**
 * Simple test for datahound.no authentication
 */

// Test direct authentication to datahound.no
echo "=== NPK Valpeliste Authentication Test ===\n\n";

$login_url = 'https://pointer.datahound.no/admin';
$login_action_url = 'https://pointer.datahound.no/admin/index/auth';
$username = 'demo';
$password = 'demo';

echo "Testing authentication to: $login_url\n";
echo "Login action URL: $login_action_url\n";
echo "Username: $username\n";
echo "Password: $password\n\n";

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
echo "   Page size: " . strlen($login_page) . " bytes\n";

// Look for CSRF token
$csrf_token = '';
if (preg_match('/<input[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']*)["\']/', $login_page, $matches)) {
    $csrf_token = $matches[1];
    echo "   CSRF token found: " . substr($csrf_token, 0, 10) . "...\n";
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
if ($redirect_url) {
    echo "   Redirect to: $redirect_url\n";
}

echo "   Response size: " . strlen($login_response) . " bytes\n";

// Check if login was successful
$login_successful = false;
if ($login_http_code == 302 || $login_http_code == 301) {
    $login_successful = true;
    echo "   ✅ Login appears successful (redirect response)\n";
} elseif (strpos($login_response, 'dashboard') !== false || 
          strpos($login_response, 'admin') !== false ||
          strpos($login_response, 'logout') !== false) {
    $login_successful = true;
    echo "   ✅ Login appears successful (admin content detected)\n";
} else {
    echo "   ❌ Login may have failed\n";
}

// Test API endpoint
if ($login_successful) {
    echo "\n3. Testing API endpoint...\n";
    
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
    echo "   Response size: " . strlen($api_response) . " bytes\n";
    
    if ($api_http_code == 200) {
        echo "   ✅ API call successful\n";
        
        // Try to decode JSON
        $data = json_decode($api_response, true);
        if ($data !== null) {
            echo "   ✅ Valid JSON response\n";        echo "   Data keys: " . implode(', ', array_keys($data)) . "\n";
        
        if (isset($data['dogs']) && is_array($data['dogs'])) {
            echo "   ✅ Found dogs array with " . count($data['dogs']) . " entries\n";
            
            // Check first dog for badge fields
            if (!empty($data['dogs'])) {
                $first_dog = $data['dogs'][0];
                echo "   First dog KUID: " . (isset($first_dog['KUID']) ? $first_dog['KUID'] : 'N/A') . "\n";
                echo "   First dog kennel: " . (isset($first_dog['kennel']) ? $first_dog['kennel'] : 'N/A') . "\n";
                
                // Check for badge fields
                $badge_fields = ['FatherPrem', 'MotherPrem', 'premie', 'jakt'];
                foreach ($badge_fields as $field) {
                    if (isset($first_dog[$field])) {
                        echo "   Badge field '$field': " . substr($first_dog[$field], 0, 50) . "...\n";
                    }
                }
            }
        } else {
            echo "   ⚠️  No obvious puppy data in response\n";
        }
        } else {
            echo "   ❌ Invalid JSON response\n";
            echo "   First 200 chars: " . substr($api_response, 0, 200) . "\n";
        }
    } else {
        echo "   ❌ API call failed\n";
        echo "   Response: " . substr($api_response, 0, 200) . "\n";
    }
}

curl_close($ch);

echo "\n=== Test Complete ===\n";
