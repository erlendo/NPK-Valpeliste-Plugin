<?php
// Vis FAKTISK rå data fra Datahound API
require_once 'includes/admin-settings.php';

$api_user = get_api_user();
$api_password = get_api_password();

if (!$api_user || !$api_password) {
    die("API credentials ikke satt");
}

echo "<h2>FAKTISK DATA FRA DATAHOUND API</h2>\n";

// Auth
$auth_url = 'https://pointer.datahound.no/admin/plugins/authenticateuser';
$auth_data = json_encode(['username' => $api_user, 'password' => $api_password]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $auth_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $auth_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
    CURLOPT_TIMEOUT => 30,
]);

echo "1. Getting login page...\n";
$login_page = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "   Status: HTTP $status\n";
echo "   Page size: " . strlen($login_page) . " bytes\n";

if ($status == 200) {
    // Perform login
    $login_data = [
        'admin_username' => 'demo',
        'admin_password' => 'demo',
        'login' => 'login'
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://pointer.datahound.no/admin/index/auth',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($login_data)
    ]);
    
    echo "\n2. Attempting login...\n";
    $login_response = curl_exec($ch);
    $login_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "   Login response: HTTP $login_status\n";
    echo "   Response size: " . strlen($login_response) . " bytes\n";
    
    // Check if login was successful
    if (stripos($login_response, 'admin') !== false && stripos($login_response, 'dashboard') !== false) {
        echo "   ✅ Login appears successful (admin content detected)\n";
        
        // Now get the API data
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://pointer.datahound.no/admin/product/getvalpeliste',
            CURLOPT_POST => false,
            CURLOPT_POSTFIELDS => null
        ]);
        
        echo "\n3. Getting API data...\n";
        $api_response = curl_exec($ch);
        $api_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "   API response: HTTP $api_status\n";
        echo "   Response size: " . strlen($api_response) . " bytes\n";
        
        if ($api_status == 200) {
            $data = json_decode($api_response, true);
            
            if ($data && isset($data['dogs'])) {
                echo "   ✅ Valid JSON response\n";
                echo "   Found " . count($data['dogs']) . " dogs\n\n";
                
                // Search for Wild Desert Storm
                $found = false;
                foreach ($data['dogs'] as $index => $dog) {
                    $search_string = json_encode($dog);
                    
                    if (stripos($search_string, 'Wild Desert Storm') !== false || 
                        stripos($search_string, 'NO46865/21') !== false ||
                        stripos($search_string, 'Huldreveien') !== false) {
                        
                        echo "=== FOUND match in dog {$index} ===\n";
                        echo "KUID: " . ($dog['KUID'] ?? 'N/A') . "\n";
                        echo "Kennel: " . ($dog['kennel'] ?? 'N/A') . "\n";
                        echo "Father: " . ($dog['FatherName'] ?? 'N/A') . "\n";
                        echo "Father Reg: " . ($dog['FatherReg'] ?? 'N/A') . "\n";
                        echo "Mother: " . ($dog['MotherName'] ?? 'N/A') . "\n";
                        echo "Mother Reg: " . ($dog['MotherReg'] ?? 'N/A') . "\n";
                        echo "Father eliteh: '" . ($dog['Fathereliteh'] ?? '') . "'\n";
                        echo "Mother eliteh: '" . ($dog['Mothereliteh'] ?? '') . "'\n";
                        echo "Father avlsh: '" . ($dog['Fatheravlsh'] ?? '') . "'\n";
                        echo "Mother avlsh: '" . ($dog['Motheravlsh'] ?? '') . "'\n";
                        echo "\n";
                        $found = true;
                    }
                }
                
                if (!$found) {
                    echo "=== Wild Desert Storm / NO46865/21 / Huldreveien NOT FOUND ===\n";
                    echo "Current dogs in the system:\n\n";
                    
                    foreach ($data['dogs'] as $index => $dog) {
                        echo "Dog {$index}:\n";
                        echo "  KUID: " . ($dog['KUID'] ?? 'N/A') . "\n";
                        echo "  Kennel: " . ($dog['kennel'] ?? 'N/A') . "\n";
                        echo "  Father: " . ($dog['FatherName'] ?? 'N/A') . " (" . ($dog['FatherReg'] ?? 'N/A') . ")\n";
                        echo "  Mother: " . ($dog['MotherName'] ?? 'N/A') . " (" . ($dog['MotherReg'] ?? 'N/A') . ")\n";
                        
                        // Check for any eliteh badges
                        $father_eliteh = $dog['Fathereliteh'] ?? '';
                        $mother_eliteh = $dog['Mothereliteh'] ?? '';
                        if (!empty($father_eliteh) || !empty($mother_eliteh)) {
                            echo "  ** ELITEH BADGES: Father='{$father_eliteh}', Mother='{$mother_eliteh}'\n";
                        }
                        
                        echo "\n";
                    }
                }
                
            } else {
                echo "   ❌ Invalid JSON response\n";
                echo "   Raw response: " . substr($api_response, 0, 500) . "\n";
            }
        } else {
            echo "   ❌ API call failed\n";
        }
    } else {
        echo "   ❌ Login failed (no admin content detected)\n";
    }
} else {
    echo "   ❌ Login page access failed\n";
}

curl_close($ch);
echo "\n=== Data Dump Complete ===\n";
?>
