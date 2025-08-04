<?php
/**
 * Detailed API response inspection
 */

// Test direct authentication and API response
echo "=== NPK Valpeliste API Response Analysis ===\n\n";

$login_url = 'https://pointer.datahound.no/admin';
$login_action_url = 'https://pointer.datahound.no/admin/index/auth';
$api_url = 'https://pointer.datahound.no/admin/product/getvalpeliste';
$username = 'demo';
$password = 'demo';

// Initialize cURL
$ch = curl_init();

// Get login page and login
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

// Prepare and submit login
$login_data = http_build_query([
    'admin_username' => $username,
    'admin_password' => $password,
    'login' => 'login'
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

$login_response = curl_exec($ch);

// Test API endpoint
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

curl_close($ch);

if ($api_http_code == 200) {
    echo "✅ API call successful\n";
    
    $data = json_decode($api_response, true);
    if ($data !== null) {
        echo "✅ Valid JSON response\n\n";
        
        echo "=== API Response Structure ===\n";
        echo "Total Count: " . (isset($data['totalCount']) ? $data['totalCount'] : 'not set') . "\n";
        
        if (isset($data['dogs']) && is_array($data['dogs'])) {
            echo "Dogs array: " . count($data['dogs']) . " entries\n\n";
            
            // Show first few entries
            $sample_size = min(3, count($data['dogs']));
            echo "=== Sample Entries (first $sample_size) ===\n";
            
            for ($i = 0; $i < $sample_size; $i++) {
                $dog = $data['dogs'][$i];
                echo "\n--- Entry " . ($i + 1) . " ---\n";
                
                // Show key fields
                foreach ($dog as $key => $value) {
                    if (is_array($value)) {
                        echo "$key: [array with " . count($value) . " items]\n";
                    } else {
                        $display_value = is_string($value) ? substr($value, 0, 100) : $value;
                        echo "$key: $display_value\n";
                    }
                }
            }
            
            // Check for badge-related fields
            echo "\n=== Badge Analysis ===\n";
            $badge_fields = [];
            $first_dog = isset($data['dogs'][0]) ? $data['dogs'][0] : null;
            
            if ($first_dog) {
                foreach ($first_dog as $key => $value) {
                    if (stripos($key, 'badge') !== false || 
                        stripos($key, 'health') !== false ||
                        stripos($key, 'test') !== false ||
                        stripos($key, 'cert') !== false ||
                        stripos($key, 'award') !== false) {
                        $badge_fields[] = $key;
                    }
                }
            }
            
            if (!empty($badge_fields)) {
                echo "Found potential badge fields: " . implode(', ', $badge_fields) . "\n";
            } else {
                echo "No obvious badge fields found\n";
                echo "All fields in first entry: " . implode(', ', array_keys($first_dog)) . "\n";
            }
            
        } else {
            echo "No 'dogs' array found in response\n";
        }
        
        echo "\n=== Raw Data Sample ===\n";
        echo substr($api_response, 0, 500) . "...\n";
        
    } else {
        echo "❌ Invalid JSON response\n";
        echo "Response: " . substr($api_response, 0, 500) . "\n";
    }
} else {
    echo "❌ API call failed with HTTP $api_http_code\n";
}

echo "\n=== Analysis Complete ===\n";
