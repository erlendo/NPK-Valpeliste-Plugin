<?php
// Enkelt script for å finne individuelle badge-felter
echo "=== SØKER ETTER INDIVIDUELLE BADGE-FELTER ===\n";

// Simuler API data basert på det vi vet
$test_data = [
    'dogs' => [
        [
            'id' => '1',
            'kennel' => 'Test Kennel',
            'FatherName' => 'Test Far',
            'MotherName' => 'Test Mor',
            'father' => 'NO12345/20',
            'mother' => 'NO67890/19',
            
            // Disse feltene vet vi finnes på kull-nivå
            'avlsh' => '0',     // Kull-nivå badge
            'eliteh' => '1',    // Kull-nivå badge
            'premie' => '39',   // Fars premie
            'PremieM' => '31',  // Mors premie
            
            // Andre felt
            'althdFather' => '108',
            'althdMother' => '111',
            'fatherHD' => '112',
            'motherHD' => '116',
            'jaktindF' => '114',
            'jaktindM' => '115',
            'standindF' => '104',
            'standindM' => '109',
            'althdF' => '112',
            'althdM' => '116',
        ]
    ]
];

echo "HYPOTESE: Badge-felter kan være individuelle med suffikser F/M eller Father/Mother\n\n";

echo "Mulige individuelle badge-felter å lete etter:\n";
$possible_fields = [
    'avlshF', 'avlshM',           // Avlshund far/mor
    'avlshFather', 'avlshMother', // Avlshund father/mother  
    'elitehF', 'elitehM',         // Elite far/mor
    'elitehFather', 'elitehMother', // Elite father/mother
    'fatherAvlsh', 'motherAvlsh', // Father/mother avlsh
    'fatherEliteh', 'motherEliteh', // Father/mother eliteh
    'Favlsh', 'Mavlsh',           // F/M avlsh
    'Feliteh', 'Meliteh',         // F/M eliteh
];

foreach ($possible_fields as $field) {
    echo "- $field\n";
}

echo "\nVi trenger å sjekke faktisk API data for disse feltene!\n";

// Test om vi kan finne noen av disse i eksisterende data
echo "\n=== TEST MED EKSISTERENDE SHOW_RAW_API SCRIPT ===\n";

// Kjør show_raw_api.php og parse output
ob_start();
include 'show_raw_api.php';
$output = ob_get_clean();

// Søk etter potensielle individuelle badge-felter
$lines = explode("\n", $output);
foreach ($lines as $line) {
    foreach ($possible_fields as $field) {
        if (stripos($line, $field) !== false) {
            echo "FUNNET: $line\n";
        }
    }
}

echo "\n=== MANUELL SØKING I OUTPUT ===\n";
// Søk etter patterns som kan være individuelle badges
foreach ($lines as $line) {
    if (preg_match('/avlsh|elite/i', $line) && preg_match('/F|M|father|mother/i', $line)) {
        echo "POTENSIELT: $line\n";
    }
}
?>
