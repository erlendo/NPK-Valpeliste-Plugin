<?php
// Søk etter ALLE badge-relaterte felter i API dataen
require_once 'includes/admin-settings.php';

// Hent credentials
$api_user = get_api_user();
$api_password = get_api_password();

if (!$api_user || !$api_password) {
    die("API credentials ikke satt i WordPress admin");
}

echo "<h2>KOMPLETTANALYSE: Alle badge-felter fra Datahound API</h2>\n";

// Autentiser
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

$auth_response = curl_exec($ch);
$auth_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($auth_status !== 200) {
    die("Autentisering feilet: HTTP $auth_status");
}

// Hent data
$data_url = 'https://pointer.datahound.no/admin/plugins/valpeliste';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $data_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');

$response = curl_exec($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status_code !== 200) {
    die("API kall feilet: HTTP $status_code");
}

$data = json_decode($response, true);
if (!$data || !isset($data['dogs']) || !is_array($data['dogs'])) {
    die("Ugyldig API respons");
}

echo "<h3>SØKER ETTER ALLE BADGE-FELTTYPER</h3>\n";

$all_keys = [];
foreach ($data['dogs'] as $dog) {
    $all_keys = array_merge($all_keys, array_keys($dog));
}
$unique_keys = array_unique($all_keys);

// Filtrer badge-relaterte felter
$badge_related = [];
foreach ($unique_keys as $key) {
    if (stripos($key, 'avlsh') !== false || 
        stripos($key, 'elite') !== false || 
        stripos($key, 'badge') !== false ||
        stripos($key, 'status') !== false ||
        (stripos($key, 'father') !== false && (stripos($key, 'avl') !== false || stripos($key, 'elit') !== false)) ||
        (stripos($key, 'mother') !== false && (stripos($key, 'avl') !== false || stripos($key, 'elit') !== false))) {
        $badge_related[] = $key;
    }
}

echo "<strong>Badge-relaterte felt funnet:</strong>\n";
foreach ($badge_related as $field) {
    echo "- $field\n";
}

echo "\n<h3>DETALJERT ANALYSE AV FØRSTE 3 HUNDER</h3>\n";

foreach (array_slice($data['dogs'], 0, 3) as $i => $dog) {
    echo "\n<strong>Hund $i: {$dog['kennel']}</strong>\n";
    echo "Far: " . (isset($dog['FatherName']) ? $dog['FatherName'] : 'N/A') . "\n";
    echo "Mor: " . (isset($dog['MotherName']) ? $dog['MotherName'] : 'N/A') . "\n";
    
    // Print ALLE felter som inneholder 'avlsh' eller 'elite'
    foreach ($dog as $key => $value) {
        if (stripos($key, 'avlsh') !== false || stripos($key, 'elite') !== false) {
            echo "  $key: " . (is_null($value) || $value === '' ? 'NULL/TOM' : "'$value'") . "\n";
        }
    }
    
    // Print alle Father/Mother relaterte felter
    echo "Far-relaterte felter:\n";
    foreach ($dog as $key => $value) {
        if (stripos($key, 'father') !== false) {
            echo "  $key: " . (is_null($value) || $value === '' ? 'NULL/TOM' : "'$value'") . "\n";
        }
    }
    
    echo "Mor-relaterte felter:\n";
    foreach ($dog as $key => $value) {
        if (stripos($key, 'mother') !== false) {
            echo "  $key: " . (is_null($value) || $value === '' ? 'NULL/TOM' : "'$value'") . "\n";
        }
    }
    
    echo "-----\n";
}

// La oss også se på alle mulige feltnavn i case det er noe vi har oversett
echo "\n<h3>ALLE FELTNAVN I API (for referanse)</h3>\n";
sort($unique_keys);
foreach ($unique_keys as $key) {
    echo "- $key\n";
}
?>
