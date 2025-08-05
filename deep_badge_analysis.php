<?php
/**
 * Deep analysis of all fields that might contain individual badge data
 */

echo "=== Dyp Analyse av Badge Data i Eksisterende API ===\n\n";

// Use existing working authentication
require_once 'includes/data-processing.php';

$data = fetch_valpeliste_data();

if ($data && isset($data['dogs'])) {
    echo "Analyserer " . count($data['dogs']) . " kull for individuelle badge mønstre\n\n";
    
    foreach ($data['dogs'] as $index => $dog) {
        echo "=== KULL {$index}: " . ($dog['kennel'] ?? 'N/A') . " ===\n";
        echo "Far: " . ($dog['FatherName'] ?? 'N/A') . " (" . ($dog['father'] ?? 'N/A') . ")\n";
        echo "Mor: " . ($dog['MotherName'] ?? 'N/A') . " (" . ($dog['mother'] ?? 'N/A') . ")\n";
        
        // Show all numeric fields that might represent badges
        echo "\nNUMERISKE BADGE-FELT:\n";
        $numeric_fields = ['avlsh', 'eliteh', 'premie', 'jakt', 'PremieM', 'jaktM'];
        foreach ($numeric_fields as $field) {
            if (isset($dog[$field])) {
                echo "  {$field}: '{$dog[$field]}'\n";
            }
        }
        
        // Show all fields with F/M suffix (might be father/mother specific)
        echo "\nFELT MED F/M SUFFIKS:\n";
        foreach ($dog as $field => $value) {
            if (preg_match('/[a-zA-Z]+[FM]$/', $field)) {
                echo "  {$field}: '{$value}'\n";
            }
        }
        
        // Show all fields with numbers that could be individual scores
        echo "\nALLE NUMERISKE FELT:\n";
        foreach ($dog as $field => $value) {
            if (is_numeric($value) && $value != '0' && $value != '') {
                echo "  {$field}: {$value}\n";
            }
        }
        
        echo "\n";
        
        // Focus on known Elite/Avls dogs
        if (stripos($dog['FatherName'] ?? '', 'Wild Desert Storm') !== false ||
            stripos($dog['FatherName'] ?? '', 'Cacciatore') !== false ||
            stripos($dog['MotherName'] ?? '', 'Philippa') !== false) {
            
            echo "*** VIKTIG KULL - DETALJERT ANALYSE ***\n";
            echo "ALLE FELT:\n";
            foreach ($dog as $field => $value) {
                if (is_string($value) && strlen($value) < 100) {
                    echo "  {$field}: '{$value}'\n";
                } elseif (is_numeric($value)) {
                    echo "  {$field}: {$value}\n";
                }
            }
            echo "\n";
        }
    }
    
    // Pattern analysis across all dogs
    echo "=== MØNSTER-ANALYSE PÅ TVERS AV ALLE KULL ===\n";
    
    $field_patterns = [];
    foreach ($data['dogs'] as $dog) {
        foreach ($dog as $field => $value) {
            if (!isset($field_patterns[$field])) {
                $field_patterns[$field] = [];
            }
            if (!in_array($value, $field_patterns[$field])) {
                $field_patterns[$field][] = $value;
            }
        }
    }
    
    echo "Felt som kan indikere individuelle forskjeller:\n";
    foreach ($field_patterns as $field => $values) {
        // Show fields that have different values (might indicate individual differences)
        if (count($values) > 1 && 
            (stripos($field, 'avl') !== false || 
             stripos($field, 'elite') !== false ||
             stripos($field, 'prem') !== false ||
             preg_match('/[FM]$/', $field))) {
            
            echo "  {$field}: " . implode(', ', array_slice($values, 0, 5)) . "\n";
        }
    }
    
    // Special analysis for badge interpretation
    echo "\n=== BADGE TOLKNING HYPOTESER ===\n";
    
    echo "Hypotese 1: avlsh/eliteh gjelder moren (siden hun bærer valpene)\n";
    echo "Hypotese 2: premie/jakt vs PremieM/jaktM indikerer far vs mor\n";
    echo "Hypotese 3: Felt med F/M suffiks skiller far/mor data\n";
    echo "Hypotese 4: FatherPrem/MotherPrem inneholder visuell badge info\n";
    
    // Test specific cases
    foreach ($data['dogs'] as $dog) {
        if (isset($dog['eliteh']) && $dog['eliteh'] == '1') {
            echo "\nKull med eliteh=1:\n";
            echo "  Far: " . ($dog['FatherName'] ?? 'N/A') . "\n";
            echo "  Mor: " . ($dog['MotherName'] ?? 'N/A') . "\n";
            echo "  Premie scores - premie: " . ($dog['premie'] ?? 'N/A') . ", PremieM: " . ($dog['PremieM'] ?? 'N/A') . "\n";
            
            // If we know these specific dogs should be elite
            if (stripos($dog['FatherName'] ?? '', 'Wild Desert Storm') !== false) {
                echo "  *** Wild Desert Storm SKAL være elitehund - eliteh=1 kan bety han ER elitehund ***\n";
            }
            if (stripos($dog['FatherName'] ?? '', 'Cacciatore') !== false) {
                echo "  *** Cacciatore SKAL være elitehund - eliteh=1 kan bety han ER elitehund ***\n";
            }
            if (stripos($dog['MotherName'] ?? '', 'Philippa') !== false) {
                echo "  *** Philippa SKAL være elitehund - eliteh=1 kan bety hun ER elitehund ***\n";
            }
        }
    }
    
} else {
    echo "❌ Kunne ikke hente API data\n";
}

echo "\n=== Analyse Fullført ===\n";
?>
