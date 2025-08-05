<?php
echo "Starting debug...\n";

// Sjekk om filene finnes
$files = [
    'npk_valpeliste.php',
    'includes/helpers.php', 
    'includes/data-processing.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file finnes\n";
    } else {
        echo "❌ $file finnes IKKE\n";
    }
}

// Test include
echo "\nTesting include...\n";
try {
    require_once 'npk_valpeliste.php';
    echo "✅ Plugin loaded\n";
} catch (Exception $e) {
    echo "❌ Plugin load error: " . $e->getMessage() . "\n";
}

// Test funksjoner
$functions = ['authenticate_datahound', 'fetch_valpeliste_data', 'process_valp_data', 'get_dog_status'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func finnes\n";
    } else {
        echo "❌ $func finnes IKKE\n";
    }
}

echo "\nDone.\n";
?>
