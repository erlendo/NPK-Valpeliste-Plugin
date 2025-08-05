<?php
/**
 * NPK Valpeliste Cache Clear Utility
 * 
 * Simple script to clear NPK Valpeliste cache
 * Usage: Visit this file in browser to clear cache
 */

// Security check - only allow local access or logged-in admins
if (!isset($_SERVER['HTTP_HOST']) || 
    (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) && 
     !current_user_can('manage_options'))) {
    die('Access denied');
}

// WordPress bootstrap
require_once '../../../wp-config.php';

// Clear NPK Valpeliste cache
$cleared = delete_transient('pointer_valpeliste_live_data');
$cleared_html = delete_transient('pointer_valpeliste_html');

// Clear WordPress object cache if available
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>NPK Valpeliste Cache Clear</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { background: #e6ffe6; border: 1px solid #4caf50; padding: 15px; border-radius: 5px; }
        .info { background: #f0f8ff; border: 1px solid #0066cc; padding: 15px; border-radius: 5px; margin-top: 20px; }
        h1 { color: #333; }
        .button { background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; display: inline-block; margin: 10px 5px 0 0; }
        .button:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>🔄 NPK Valpeliste Cache Clear</h1>
    
    <div class="success">
        <h3>✅ Cache Cleared Successfully!</h3>
        <p><strong>Data cache:</strong> <?php echo $cleared ? 'Cleared' : 'Was already empty'; ?></p>
        <p><strong>HTML cache:</strong> <?php echo $cleared_html ? 'Cleared' : 'Was already empty'; ?></p>
        <p><strong>Object cache:</strong> Flushed</p>
    </div>
    
    <div class="info">
        <h4>📋 What happens next:</h4>
        <ul>
            <li>Next time the valpeliste shortcode is displayed, it will fetch fresh data from datahound.no</li>
            <li>Cache will be rebuilt automatically and stored for 30 minutes</li>
            <li>You can visit the page with the shortcode to verify the update</li>
        </ul>
        
        <h4>🔗 Quick Actions:</h4>
        <a href="<?php echo home_url(); ?>" class="button">Go to Website</a>
        <a href="<?php echo admin_url('options-general.php?page=npk_valpeliste_settings'); ?>" class="button">Plugin Settings</a>
        <a href="<?php echo $_SERVER['REQUEST_URI']; ?>" class="button">Clear Cache Again</a>
    </div>
    
    <p><em>Generated at <?php echo date('Y-m-d H:i:s'); ?></em></p>
</body>
</html>
