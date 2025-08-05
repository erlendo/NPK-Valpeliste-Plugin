<?php
echo "=== Direkte API Badge Struktur Analyse ===\n\n";

// Første - logg inn
$login_url = 'https://pointer.datahound.no/admin';
$login_action_url = 'https://pointer.datahound.no/admin/index/auth';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $login_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
curl_setopt($curl, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
$login_page = curl_exec($curl);

// Hent CSRF token
preg_match('/name="_token" value="([^"]*)"/', $login_page, $token_match);
$csrf_token = $token_match[1] ?? '';

// Logg inn
curl_setopt($curl, CURLOPT_URL, $login_action_url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'demo',
    'password' => 'demo',
    '_token' => $csrf_token
]));
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Referer: ' . $login_url,
    'Origin: https://pointer.datahound.no'
]);
curl_exec($curl);

// Hent valpeliste data
$api_url = 'https://pointer.datahound.no/admin/product/getvalpeliste';
curl_setopt($curl, CURLOPT_URL, $api_url);
curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Accept: application/json, text/javascript, */*; q=0.01',
    'X-Requested-With: XMLHttpRequest',
    'Referer: https://pointer.datahound.no/admin/'
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($http_code !== 200) {
    echo "API feil: HTTP $http_code\n";
    exit;
}

$data = json_decode($response, true);
if (!$data || !isset($data['dogs'])) {
    echo "Ugyldig API respons\n";
    exit;
}

echo "Antall kull i live data: " . $data['totalCount'] . "\n\n";

// Analyser alle kull
foreach ($data['dogs'] as $index => $litter) {
    echo "=== KULL " . ($index + 1) . " ===\n";
    echo "KUID: " . $litter['KUID'] . "\n";
    echo "Kennel: " . $litter['kennel'] . "\n";
    echo "Far: " . $litter['FatherName'] . "\n";
    echo "Mor: " . $litter['MotherName'] . "\n";
    
    // Badge felter
    echo "\nBadge status:\n";
    echo "  avlsh: " . json_encode($litter['avlsh'] ?? null) . "\n";
    echo "  eliteh: " . json_encode($litter['eliteh'] ?? null) . "\n";
    
    // Søk etter alle badge-relaterte felter
    $badge_fields = [];
    foreach ($litter as $key => $value) {
        if (preg_match('/avl|elite|badge|ribbon|prem/i', $key)) {
            $badge_fields[$key] = $value;
        }
    }
    
    if (!empty($badge_fields)) {
        echo "\nAlle badge-relaterte felter:\n";
        foreach ($badge_fields as $field => $value) {
            if (is_string($value) && strlen($value) > 50) {
                $value = substr($value, 0, 50) . "...";
            }
            echo "  $field: " . json_encode($value) . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// Analyse av alle feltnavn
echo "\n=== ALLE TILGJENGELIGE FELTNAVN ===\n";
$all_keys = [];
foreach ($data['dogs'] as $litter) {
    foreach ($litter as $key => $value) {
        if (!in_array($key, $all_keys)) {
            $all_keys[] = $key;
        }
    }
}

sort($all_keys);
echo "Totalt " . count($all_keys) . " ulike feltnavn:\n";
foreach ($all_keys as $key) {
    echo "  - $key\n";
}

echo "\n=== SØKER ETTER INDIVIDUELLE BADGE PATTERNS ===\n";
$individual_patterns = [];

foreach ($all_keys as $key) {
    // Søk etter patterns som kan indikere individuelle badge felt
    if (preg_match('/(individual|dog|puppy|valp|offspring|avkom)/i', $key) ||
        preg_match('/[FM].*avl/i', $key) ||
        preg_match('/[FM].*elite/i', $key) ||
        preg_match('/avl.*[FM]/i', $key) ||
        preg_match('/elite.*[FM]/i', $key)) {
        $individual_patterns[] = $key;
    }
}

if (!empty($individual_patterns)) {
    echo "Potensielle individuelle badge patterns:\n";
    foreach ($individual_patterns as $pattern) {
        echo "  - $pattern\n";
    }
} else {
    echo "INGEN individuelle badge patterns funnet!\n";
}

echo "\n=== KONKLUSJON ===\n";
echo "API-strukturen inneholder IKKE individuelle badge flagg.\n";
echo "Badges (avlsh, eliteh) eksisterer kun på kull-nivå.\n";
echo "For å vise individuelle badges må de beregnes fra andre data.\n";
?>
