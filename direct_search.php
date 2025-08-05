<?php
echo "Starting search for Wild Desert Storm...\n";

// Direct API call to check data
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://pointer.datahound.no/admin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; NPK-Valpeliste)');

$login_page = curl_exec($ch);
$login_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Login page status: {$login_status}\n";

if ($login_status == 200) {
    // Perform login
    $login_data = [
        'admin_username' => 'demo',
        'admin_password' => 'demo',
        'login' => 'login'
    ];
    
    curl_setopt($ch, CURLOPT_URL, 'https://pointer.datahound.no/admin/index/auth');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($login_data));
    
    $login_response = curl_exec($ch);
    $auth_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Auth status: {$auth_status}\n";
    
    if ($auth_status == 200) {
        // Get API data
        curl_setopt($ch, CURLOPT_URL, 'https://pointer.datahound.no/admin/product/getvalpeliste');
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        
        $api_response = curl_exec($ch);
        $api_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo "API status: {$api_status}\n";
        
        if ($api_status == 200) {
            $data = json_decode($api_response, true);
            
            if ($data && isset($data['dogs'])) {
                echo "Found " . count($data['dogs']) . " dogs\n\n";
                
                $found = false;
                foreach ($data['dogs'] as $index => $dog) {
                    // Check all fields for Wild Desert Storm or NO46865/21
                    $dog_str = json_encode($dog);
                    if (stripos($dog_str, 'Wild Desert Storm') !== false || stripos($dog_str, 'NO46865/21') !== false) {
                        echo "=== FOUND Wild Desert Storm in dog {$index} ===\n";
                        echo "KUID: " . ($dog['KUID'] ?? 'N/A') . "\n";
                        echo "Kennel: " . ($dog['kennel'] ?? 'N/A') . "\n";
                        echo "Father: " . ($dog['FatherName'] ?? 'N/A') . " (" . ($dog['FatherReg'] ?? 'N/A') . ")\n";
                        echo "Mother: " . ($dog['MotherName'] ?? 'N/A') . " (" . ($dog['MotherReg'] ?? 'N/A') . ")\n";
                        echo "Father eliteh: " . ($dog['Fathereliteh'] ?? 'N/A') . "\n";
                        echo "Mother eliteh: " . ($dog['Mothereliteh'] ?? 'N/A') . "\n";
                        echo "Father avlsh: " . ($dog['Fatheravlsh'] ?? 'N/A') . "\n";
                        echo "Mother avlsh: " . ($dog['Motheravlsh'] ?? 'N/A') . "\n";
                        $found = true;
                        echo "\n";
                    }
                }
                
                if (!$found) {
                    echo "Wild Desert Storm not found. Showing all dogs:\n\n";
                    foreach ($data['dogs'] as $index => $dog) {
                        echo "Dog {$index}: " . ($dog['kennel'] ?? 'N/A') . "\n";
                        echo "  Father: " . ($dog['FatherName'] ?? 'N/A') . " (" . ($dog['FatherReg'] ?? 'N/A') . ")\n";
                        echo "  Mother: " . ($dog['MotherName'] ?? 'N/A') . " (" . ($dog['MotherReg'] ?? 'N/A') . ")\n";
                        echo "\n";
                    }
                }
            } else {
                echo "Invalid JSON response\n";
            }
        } else {
            echo "API call failed\n";
        }
    } else {
        echo "Authentication failed\n";
    }
} else {
    echo "Login page access failed\n";
}

curl_close($ch);
echo "Search complete.\n";
?>
