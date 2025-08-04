<?php
// Test av datahound.no API tilkobling
// Simulerer WordPress miljø for testing

// Enkel wp_remote_post simulering med cURL
function test_api_connection() {
    echo "🔍 Tester tilkobling til datahound.no API...\n\n";
    
    $login_url = 'https://pointer.datahound.no/admin/index/auth';
    $api_endpoint = 'https://pointer.datahound.no/admin/product/getvalpeliste';
    
    // Step 1: Login med cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $login_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'admin_username' => 'demo',
        'admin_password' => 'demo',
        'login' => 'login'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
    curl_setopt($ch, CURLOPT_USERAGENT, 'NPK Valpeliste Plugin Test');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $login_response = curl_exec($ch);
    $login_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Login status: $login_status\n";
    
    if ($login_status == 200) {
        echo "✅ Login successful\n";
        echo "🔄 Henter valpeliste...\n\n";
        
        // Step 2: Hent valpeliste med cookies
        curl_setopt($ch, CURLOPT_URL, $api_endpoint);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest'
        ]);
        
        $api_response = curl_exec($ch);
        $api_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo "API status: $api_status\n";
        
        if ($api_status == 200) {
            $data = json_decode($api_response, true);
            
            if ($data && is_array($data)) {
                echo "✅ API-data mottatt!\n";
                echo "📊 Data type: " . gettype($data) . "\n";
                
                // Sjekk struktur
                if (isset($data['dogs']) && is_array($data['dogs'])) {
                    echo "🐕 Antall hunder: " . count($data['dogs']) . "\n\n";
                    
                    // Vis første hund
                    if (!empty($data['dogs'])) {
                        $first_dog = $data['dogs'][0];
                        echo "📋 Første hund:\n";
                        echo "==========================================\n";
                        foreach ($first_dog as $key => $value) {
                            if (is_string($value) || is_numeric($value)) {
                                echo "$key: $value\n";
                            }
                        }
                    }
                } else if (is_array($data)) {
                    echo "📊 Array med " . count($data) . " elementer\n";
                    if (!empty($data)) {
                        echo "🔍 Første element:\n";
                        print_r(array_slice($data, 0, 1));
                    }
                } else {
                    echo "⚠️ Uventet data format\n";
                    echo "Data preview: " . substr($api_response, 0, 200) . "...\n";
                }
            } else {
                echo "❌ Kunne ikke dekode JSON data\n";
                echo "Response preview: " . substr($api_response, 0, 200) . "...\n";
            }
        } else {
            echo "❌ API request failed\n";
            echo "Response: " . substr($api_response, 0, 200) . "...\n";
        }
    } else {
        echo "❌ Login failed\n";
        echo "Response: " . substr($login_response, 0, 200) . "...\n";
    }
    
    curl_close($ch);
    
    // Cleanup
    if (file_exists('/tmp/cookies.txt')) {
        unlink('/tmp/cookies.txt');
    }
}

test_api_connection();
?>
