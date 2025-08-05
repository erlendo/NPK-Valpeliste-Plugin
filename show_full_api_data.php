<?php
// VISER FAKTISK DATA FRA DATAHOUND
require_once 'includes/admin-settings.php';

$api_user = get_api_user();
$api_password = get_api_password();

if (!$api_user || !$api_password) {
    die("API credentials ikke satt");
}

echo "<h1>FAKTISK DATAHOUND API DATA</h1>\n";

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
    die("Auth failed: $auth_status");
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
    die("API call failed: $status_code");
}

echo "<h2>RÅ JSON RESPONS FRA DATAHOUND:</h2>\n";
echo "<textarea style='width:100%; height:300px; font-family:monospace; font-size:12px;'>\n";
echo htmlspecialchars($response);
echo "</textarea>\n";

$data = json_decode($response, true);
if (!$data) {
    die("Could not parse JSON");
}

echo "<h2>FORMATERT JSON:</h2>\n";
echo "<textarea style='width:100%; height:400px; font-family:monospace; font-size:12px;'>\n";
echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "</textarea>\n";

if (isset($data['dogs']) && is_array($data['dogs'])) {
    echo "<h2>ANALYSE AV STRUKTUR:</h2>\n";
    echo "<p><strong>Antall kull:</strong> " . count($data['dogs']) . "</p>\n";
    
    if (count($data['dogs']) > 0) {
        $first_dog = $data['dogs'][0];
        echo "<p><strong>Felt i hvert kull:</strong></p>\n";
        $keys = array_keys($first_dog);
        sort($keys);
        
        echo "<table border='1' style='border-collapse:collapse;'>\n";
        echo "<tr><th>Felt</th><th>Verdi (første kull)</th></tr>\n";
        foreach ($keys as $key) {
            $value = $first_dog[$key];
            if (is_string($value) && strlen($value) > 100) {
                $value = substr($value, 0, 100) . '...';
            }
            echo "<tr><td><strong>$key</strong></td><td>" . htmlspecialchars(json_encode($value)) . "</td></tr>\n";
        }
        echo "</table>\n";
        
        echo "<h3>KULL IDENTIFIKATORER:</h3>\n";
        foreach (['id', 'KUID', 'kennel', 'FatherName', 'MotherName', 'father', 'mother'] as $id_field) {
            if (isset($first_dog[$id_field])) {
                echo "<p><strong>$id_field:</strong> " . htmlspecialchars($first_dog[$id_field]) . "</p>\n";
            }
        }
    }
} 
?>
