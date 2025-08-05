<?php
// Minimal script for å vise RAW JSON struktur
require_once 'includes/admin-settings.php';

$api_user = get_api_user();
$api_password = get_api_password();

if (!$api_user || !$api_password) {
    die("API credentials ikke satt");
}

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

$auth_response = curl_exec($ch);
curl_close($ch);

// Get data
$data_url = 'https://pointer.datahound.no/admin/plugins/valpeliste';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $data_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');

$response = curl_exec($ch);
curl_close($ch);

// Print raw JSON for first dog only
$data = json_decode($response, true);
if ($data && isset($data['dogs'][0])) {
    echo "=== RAW JSON for første hund ===\n";
    echo json_encode($data['dogs'][0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    echo "\n\n=== ALLE FELT-NAVN ===\n";
    $keys = array_keys($data['dogs'][0]);
    sort($keys);
    foreach ($keys as $key) {
        echo "$key\n";
    }
}
?>
