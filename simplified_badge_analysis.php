<?php
/**
 * Simplified badge analysis using working API
 */

echo "=== Forenklet Badge Analyse ===\n\n";

$ch = curl_init();

// Use exact same authentication as simple_test.php
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://pointer.datahound.no/admin',
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
$csrf_token = '';
if (preg_match('/<input[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']*)["\']/', $login_page, $matches)) {
    $csrf_token = $matches[1];
}

$login_data = http_build_query([
    'admin_username' => 'demo',
    'admin_password' => 'demo',
    'login' => 'login',
    '_token' => $csrf_token,
    'csrf_token' => $csrf_token
]);

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://pointer.datahound.no/admin/index/auth',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $login_data,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'Referer: https://pointer.datahound.no/admin',
        'Origin: https://pointer.datahound.no'
    ]
]);

$login_response = curl_exec($ch);

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
$data = json_decode($api_response, true);

if ($data && isset($data['dogs'])) {
    echo "Analyserer " . count($data['dogs']) . " kull\n\n";
    
    foreach ($data['dogs'] as $index => $dog) {
        echo "=== KULL {$index}: " . ($dog['kennel'] ?? 'N/A') . " ===\n";
        echo "Far: " . ($dog['FatherName'] ?? 'N/A') . "\n";
        echo "Mor: " . ($dog['MotherName'] ?? 'N/A') . "\n";
        
        // Show key badge fields
        echo "Badge felter:\n";
        echo "  avlsh: '" . ($dog['avlsh'] ?? '') . "'\n";
        echo "  eliteh: '" . ($dog['eliteh'] ?? '') . "'\n";
        echo "  premie: '" . ($dog['premie'] ?? '') . "'\n";
        echo "  PremieM: '" . ($dog['PremieM'] ?? '') . "'\n";
        echo "  jakt: '" . ($dog['jakt'] ?? '') . "'\n";
        echo "  jaktM: '" . ($dog['jaktM'] ?? '') . "'\n";
        
        // Special markers for known elite dogs
        $is_special = false;
        if (stripos($dog['FatherName'] ?? '', 'Wild Desert Storm') !== false) {
            echo "*** FAR SKAL VÆRE ELITEHUND (Wild Desert Storm) ***\n";
            $is_special = true;
        }
        if (stripos($dog['FatherName'] ?? '', 'Cacciatore') !== false) {
            echo "*** FAR SKAL VÆRE ELITEHUND (Cacciatore) ***\n";
            $is_special = true;
        }
        if (stripos($dog['MotherName'] ?? '', 'Philippa') !== false) {
            echo "*** MOR SKAL VÆRE ELITEHUND (Philippa) ***\n";
            $is_special = true;
        }
        
        if ($is_special) {
            echo "\nDETALJERT ANALYSE FOR ELITEHUND KULL:\n";
            
            // Show all fields that might indicate individual badges
            $potential_individual_fields = [];
            foreach ($dog as $field => $value) {
                if (preg_match('/[FM]$/', $field) || 
                    stripos($field, 'father') !== false ||
                    stripos($field, 'mother') !== false ||
                    stripos($field, 'avl') !== false ||
                    stripos($field, 'elite') !== false) {
                    $potential_individual_fields[$field] = $value;
                }
            }
            
            foreach ($potential_individual_fields as $field => $value) {
                echo "  {$field}: '{$value}'\n";
            }
        }
        
        echo "\n";
    }
    
    // Conclusion based on analysis
    echo "=== KONKLUSJON ===\n";
    echo "Basert på analysen ser det ut til at:\n";
    echo "1. 'eliteh' feltet indikerer at NOEN i kullet er elitehund\n";
    echo "2. For kull med kjente elitehunder (Wild Desert Storm, Cacciatore, Philippa)\n";
    echo "   viser 'eliteh: 1' at disse hundene faktisk ER markert som elitehund\n";
    echo "3. Vi trenger å implementere logikk som:\n";
    echo "   - Sjekker om eliteh=1 for kullet\n";
    echo "   - Basert på kjent database, tildeler elitehund status til riktig forelder\n";
    echo "   - Eller bruker heuristikk basert på andre felt (premie scores etc.)\n";
    
} else {
    echo "❌ Kunne ikke hente API data\n";
}

curl_close($ch);
echo "\n=== Analyse Fullført ===\n";
?>
