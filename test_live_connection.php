<?php
/**
 * Test script to verify live API connection to datahound.no
 */

// Test API URLs for datahound.no
$api_urls = array(
    'https://pointer.datahound.no/api/valpeliste',
    'https://pointer.datahound.no/valpeliste.json',
    'https://pointer.datahound.no/api/puppies',
    'https://datahound.no/api/pointer/valpeliste',
    'https://datahound.no/pointer/valpeliste.json',
    'https://datahound.no/api/valpeliste/pointer'
);

echo "<h1>Testing Live API Connection to Datahound.no</h1>\n";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px;'>\n";

foreach ($api_urls as $index => $api_url) {
    echo "<h3>Test " . ($index + 1) . ": " . htmlspecialchars($api_url) . "</h3>\n";
    
    $start_time = microtime(true);
    
    // Use cURL for testing
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'NPK Valpeliste Plugin Test v1.3');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json, text/html, */*',
        'Cache-Control: no-cache'
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $end_time = microtime(true);
    $request_time = round(($end_time - $start_time) * 1000, 2);
    
    echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
    echo "<p><strong>Response Time:</strong> {$request_time}ms</p>\n";
    echo "<p><strong>Content Type:</strong> " . htmlspecialchars($content_type) . "</p>\n";
    
    if ($error) {
        echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($error) . "</p>\n";
    }
    
    if ($response) {
        echo "<p><strong>Response Size:</strong> " . strlen($response) . " bytes</p>\n";
        
        // Try to decode as JSON
        $json_data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p style='color: green;'><strong>✅ Valid JSON Response!</strong></p>\n";
            echo "<p><strong>Data Type:</strong> " . gettype($json_data) . "</p>\n";
            if (is_array($json_data)) {
                echo "<p><strong>Array Length:</strong> " . count($json_data) . "</p>\n";
                if (!empty($json_data)) {
                    echo "<p><strong>First Item Keys:</strong> " . htmlspecialchars(implode(', ', array_keys($json_data[0]))) . "</p>\n";
                }
            }
        } else {
            echo "<p style='color: orange;'><strong>⚠️ Not Valid JSON:</strong> " . json_last_error_msg() . "</p>\n";
            
            // Check if it's HTML
            if (stripos($response, '<html') !== false || stripos($response, '<!doctype') !== false) {
                echo "<p style='color: orange;'><strong>Response Type:</strong> HTML webpage</p>\n";
            } else {
                echo "<p><strong>Response Preview:</strong> " . htmlspecialchars(substr($response, 0, 200)) . "...</p>\n";
            }
        }
    } else {
        echo "<p style='color: red;'><strong>❌ No Response</strong></p>\n";
    }
    
    echo "<hr>\n";
}

echo "</div>\n";
echo "<p><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
