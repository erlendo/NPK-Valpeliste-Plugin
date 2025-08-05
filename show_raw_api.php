<?php
/**
 * Get raw API structure from Datahound
 */

// Copy working authentication from simple_test.php
$username = 'demo';
$password = 'demo';

$ch = curl_init();

echo "Getting API data...\n";

// Step 1: Get login page
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://pointer.datahound.no/admin',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR => '/tmp/cookies.txt',
    CURLOPT_COOKIEFILE => '/tmp/cookies.txt',
    CURLOPT_USERAGENT => 'NPK Valpeliste Test',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30
]);

$login_page = curl_exec($ch);
$login_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Login page: HTTP $login_code\n";

if ($login_code == 200) {
    // Step 2: Login
    $post_data = [
        'admin_username' => $username,
        'admin_password' => $password,
        'login' => 'login'
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://pointer.datahound.no/admin/index/auth',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data)
    ]);
    
    $login_response = curl_exec($ch);
    $auth_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Login: HTTP $auth_code\n";
    
    if ($auth_code == 200) {
        // Step 3: Get API data
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://pointer.datahound.no/admin/product/getvalpeliste',
            CURLOPT_POST => false,
            CURLOPT_POSTFIELDS => ''
        ]);
        
        $api_response = curl_exec($ch);
        $api_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo "API: HTTP $api_code\n";
        
        if ($api_code == 200) {
            $data = json_decode($api_response, true);
            
            if ($data && isset($data['dogs'])) {
                echo "\n=== SUCCESS - Got " . count($data['dogs']) . " dogs ===\n\n";
                
                // Show structure of first dog
                echo "First dog structure:\n";
                $first = $data['dogs'][0];
                foreach ($first as $key => $value) {
                    $val_preview = is_string($value) ? substr($value, 0, 50) : $value;
                    echo "  $key: $val_preview\n";
                }
                
                echo "\n=== Badge fields in all dogs ===\n";
                foreach ($data['dogs'] as $i => $dog) {
                    echo "Dog $i: " . (isset($dog['kennel']) ? $dog['kennel'] : 'N/A') . "\n";
                    echo "  FatherName: " . (isset($dog['FatherName']) ? $dog['FatherName'] : 'N/A') . "\n";
                    echo "  MotherName: " . (isset($dog['MotherName']) ? $dog['MotherName'] : 'N/A') . "\n";
                    echo "  avlsh: " . (isset($dog['avlsh']) ? "'{$dog['avlsh']}'" : 'ikke satt') . "\n";
                    echo "  eliteh: " . (isset($dog['eliteh']) ? "'{$dog['eliteh']}'" : 'ikke satt') . "\n";
                    echo "  premie: " . (isset($dog['premie']) ? "'{$dog['premie']}'" : 'ikke satt') . "\n";
                    echo "  PremieM: " . (isset($dog['PremieM']) ? "'{$dog['PremieM']}'" : 'ikke satt') . "\n";
                    echo "\n";
                }
                
            } else {
                echo "JSON decode failed or no dogs\n";
                echo "Raw: " . substr($api_response, 0, 200) . "\n";
            }
        } else {
            echo "API call failed\n";
        }
    } else {
        echo "Login failed\n";
    }
} else {
    echo "Login page failed\n";
}

curl_close($ch);
?>
