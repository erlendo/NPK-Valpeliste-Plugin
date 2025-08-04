<?php
/**
 * Test programmatic authentication to datahound.no - NPK Valpeliste v1.5
 */

// Simulate WordPress environment
define('ABSPATH', '/Users/erlendo/Local Sites/pointerdatabasen/app/public/');

// Mock WordPress functions
if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show) { return '6.0'; }
}
if (!function_exists('get_transient')) {
    function get_transient($key) { return false; }
}
if (!function_exists('set_transient')) {
    function set_transient($key, $data, $duration) { return true; }
}
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() { return true; }
}
if (!function_exists('esc_html')) {
    function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('home_url')) {
    function home_url() { return 'http://localhost'; }
}
if (!function_exists('error_log')) {
    function error_log($message) { echo "<p style='color: #666;'>[LOG] " . esc_html($message) . "</p>"; }
}

// Mock WP_Http_Cookie class
if (!class_exists('WP_Http_Cookie')) {
    class WP_Http_Cookie {
        public $name;
        public $value;
        public $domain;
        
        public function __construct($data) {
            $this->name = $data['name'];
            $this->value = $data['value'];
            $this->domain = isset($data['domain']) ? $data['domain'] : '';
        }
    }
}

// Mock WordPress HTTP functions
if (!function_exists('wp_remote_get')) {
    function wp_remote_get($url, $args = array()) {
        return wp_remote_request($url, array_merge($args, array('method' => 'GET')));
    }
}

if (!function_exists('wp_remote_post')) {
    function wp_remote_post($url, $args = array()) {
        return wp_remote_request($url, array_merge($args, array('method' => 'POST')));
    }
}

if (!function_exists('wp_remote_request')) {
    function wp_remote_request($url, $args = array()) {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => isset($args['redirection']) ? $args['redirection'] : 5,
            CURLOPT_TIMEOUT => isset($args['timeout']) ? $args['timeout'] : 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_USERAGENT => isset($args['user-agent']) ? $args['user-agent'] : 'NPK Test',
            CURLOPT_HEADER => true,
            CURLOPT_COOKIEJAR => 'cookies.txt',
            CURLOPT_COOKIEFILE => 'cookies.txt',
        ));
        
        if (isset($args['method']) && $args['method'] === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            if (isset($args['body'])) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($args['body']));
            }
        }
        
        if (isset($args['headers'])) {
            $headers = array();
            foreach ($args['headers'] as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        
        if (curl_error($curl)) {
            curl_close($curl);
            return new WP_Error('curl_error', curl_error($curl));
        }
        
        curl_close($curl);
        
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        
        return array(
            'headers' => wp_parse_headers($headers),
            'body' => $body,
            'response' => array('code' => $http_code)
        );
    }
}

function wp_parse_headers($header_string) {
    $headers = array();
    $lines = explode("\n", $header_string);
    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            list($key, $value) = explode(':', $line, 2);
            $headers[trim(strtolower($key))] = trim($value);
        }
    }
    return $headers;
}

if (!function_exists('wp_remote_retrieve_response_code')) {
    function wp_remote_retrieve_response_code($response) {
        if (is_wp_error($response)) return 0;
        return $response['response']['code'];
    }
}

if (!function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response) {
        if (is_wp_error($response)) return '';
        return $response['body'];
    }
}

if (!function_exists('wp_remote_retrieve_headers')) {
    function wp_remote_retrieve_headers($response) {
        if (is_wp_error($response)) return array();
        return $response['headers'];
    }
}

if (!function_exists('wp_remote_retrieve_cookies')) {
    function wp_remote_retrieve_cookies($response) {
        return array(); // Simplified for testing
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return ($thing instanceof WP_Error);
    }
}

// Mock WP_Error class
if (!class_exists('WP_Error')) {
    class WP_Error {
        private $errors = array();
        
        public function __construct($code = '', $message = '') {
            if (!empty($code)) {
                $this->errors[$code] = array($message);
            }
        }
        
        public function get_error_message() {
            $codes = array_keys($this->errors);
            if (empty($codes)) return '';
            return $this->errors[$codes[0]][0];
        }
    }
}

// Include our data processing functions
require_once 'includes/data-processing.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>NPK Valpeliste v1.5 - Programmatic Authentication Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { background: #f5f5f5; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #e6ffe6; border: 1px solid #4caf50; padding: 10px; margin: 10px 0; }
        .error { background: #ffe6e6; border: 1px solid #cc0000; padding: 10px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; }
        .info { background: #f0f8ff; border: 1px solid #0066cc; padding: 10px; margin: 10px 0; }
        pre { background: #f8f8f8; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
    </style>
</head>
<body>

<h1>🧪 NPK Valpeliste v1.5 - Programmatic Authentication Test</h1>

<div class="container">
    <h2>🔐 Testing Automatic Login to datahound.no</h2>
    <p><strong>Username:</strong> demo</p>
    <p><strong>Password:</strong> demo</p>
    <p><strong>API Endpoint:</strong> https://pointer.datahound.no/admin/product/getvalpeliste</p>
    <hr>

    <?php
    echo "<h3>Step 1: Authentication Test</h3>";
    
    // Test authentication
    $auth_result = authenticate_datahound(true);
    
    if (is_array($auth_result) && isset($auth_result['debug'])) {
        echo $auth_result['debug'];
        
        if (isset($auth_result['cookies']) && !empty($auth_result['cookies'])) {
            echo "<div class='success'>";
            echo "<h4>✅ Authentication Successful!</h4>";
            echo "<p>Got " . count($auth_result['cookies']) . " authentication cookies</p>";
            echo "</div>";
            
            echo "<hr>";
            echo "<h3>Step 2: API Data Fetch Test</h3>";
            
            // Test the API call with authentication
            $api_result = fetch_puppy_data(true, true);
            
            if (is_array($api_result) && isset($api_result['debug'])) {
                echo $api_result['debug'];
                
                if (isset($api_result['data']) && !empty($api_result['data'])) {
                    echo "<div class='success'>";
                    echo "<h4>🎯 API Call Successful!</h4>";
                    echo "<p><strong>Records Retrieved:</strong> " . count($api_result['data']) . "</p>";
                    echo "</div>";
                    
                    echo "<h4>📊 Sample Data Preview:</h4>";
                    echo "<pre>" . esc_html(json_encode(array_slice($api_result['data'], 0, 2), JSON_PRETTY_PRINT)) . "</pre>";
                } else {
                    echo "<div class='warning'>";
                    echo "<h4>⚠️ API Call Completed But No Data</h4>";
                    echo "<p>The API call was successful but returned empty data. This might be normal if there are no records.</p>";
                    echo "</div>";
                }
            } else {
                echo "<div class='error'>";
                echo "<h4>❌ API Call Failed</h4>";
                if (is_string($api_result)) {
                    echo "<p>Error: " . esc_html($api_result) . "</p>";
                } else {
                    echo "<pre>" . esc_html(print_r($api_result, true)) . "</pre>";
                }
                echo "</div>";
            }
        } else {
            echo "<div class='error'>";
            echo "<h4>❌ Authentication Failed</h4>";
            echo "<p>Could not obtain authentication cookies from datahound.no</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='error'>";
        echo "<h4>❌ Authentication Error</h4>";
        if (is_string($auth_result)) {
            echo $auth_result;
        } else {
            echo "<pre>" . esc_html(print_r($auth_result, true)) . "</pre>";
        }
        echo "</div>";
    }
    ?>

    <hr>
    <h3>🔍 WordPress Plugin Testing</h3>
    <div class="info">
        <h4>Next Steps for WordPress Testing:</h4>
        <ol>
            <li>Upload the updated plugin to WordPress</li>
            <li>Add this shortcode to a page: <code>[valpeliste debug="true"]</code></li>
            <li>The plugin will automatically authenticate using demo/demo credentials</li>
            <li>Check if live data from datahound.no is displayed</li>
        </ol>
    </div>

    <div class="warning">
        <h4>🔐 Authentication Notes:</h4>
        <ul>
            <li>The plugin now automatically logs in with username: <strong>demo</strong> and password: <strong>demo</strong></li>
            <li>No manual login to datahound.no is required</li>
            <li>Authentication cookies are obtained programmatically</li>
            <li>Each API call will re-authenticate if needed</li>
        </ul>
    </div>

    <p><em>Test completed at <?php echo date('Y-m-d H:i:s'); ?></em></p>
</div>

<p>
    <a href="test_auth_api.php" style="background:#007cba;color:white;padding:8px 16px;text-decoration:none;border-radius:3px;">🔄 Run Test Again</a>
    <a href="./" style="background:#666;color:white;padding:8px 16px;text-decoration:none;border-radius:3px;margin-left:10px;">← Back to Plugin</a>
</p>

</body>
</html>
