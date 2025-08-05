<?php
// Lagre faktisk API data til fil
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
$auth_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($auth_status !== 200) {
    file_put_contents('api_debug.txt', "Auth failed: $auth_status\n");
    die("Auth failed");
}

// Get data
$data_url = 'https://pointer.datahound.no/admin/plugins/valpeliste';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $data_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');

$response = curl_exec($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status_code !== 200) {
    file_put_contents('api_debug.txt', "API call failed: $status_code\n");
    die("API failed");
}

// Lagre RAW response
file_put_contents('api_response.json', $response);

$data = json_decode($response, true);
if (!$data) {
    file_put_contents('api_debug.txt', "Could not parse JSON\n");
    die("JSON parse failed");
}

// Lagre formatted JSON
file_put_contents('api_formatted.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "API data lagret til api_response.json og api_formatted.json\n";

if (isset($data['dogs']) && is_array($data['dogs'])) {
    echo "Fant " . count($data['dogs']) . " kull\n";
    
    if (count($data['dogs']) > 0) {
        $first_dog = $data['dogs'][0];
        $keys = array_keys($first_dog);
        
        $analysis = "ANALYSE AV FØRSTE KULL:\n";
        $analysis .= "=========================\n\n";
        
        foreach ($keys as $key) {
            $value = $first_dog[$key];
            if (is_string($value) && strlen($value) > 100) {
                $value = substr($value, 0, 100) . '...';
            }
            $analysis .= "$key: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
        }
        
        file_put_contents('api_analysis.txt', $analysis);
        echo "Analyse lagret til api_analysis.txt\n";
    }
}
?>
