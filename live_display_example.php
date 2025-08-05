<?php
/**
 * Live NPK Data Display - Uten JSON fillagring
 * Viser hvordan NPKDataExtractor kan brukes direkte i WordPress
 */

require_once 'NPKDataExtractorLive.php';

function npk_get_live_data() {
    // Hent data direkte uten å lagre til fil - OPPDATERT MED WORKING BADGES
    $extractor = new NPKDataExtractorLive(false); // debug = false for produksjon
    
    if (!$extractor->authenticate()) {
        return ['error' => 'Kunne ikke autentisere mot NPK API'];
    }
    
    // Bygg komplett datasett i minne
    $data = $extractor->buildCompleteDataset();
    
    return $data; // Returner array direkte - ingen JSON fil!
}

function npk_display_valpeliste() {
    $data = npk_get_live_data();
    
    if (isset($data['error'])) {
        return '<div class="npk-error">Feil: ' . $data['error'] . '</div>';
    }
    
    $html = '<div class="npk-valpeliste">';
    $html .= '<h2>NPK Valpeliste - Live Data</h2>';
    
    foreach ($data['kull'] as $kull) {
        $html .= '<div class="npk-kull">';
        $html .= '<h3>Kull ID: ' . $kull['kull_info']['KUID'] . '</h3>';
        
        // Mor med badges
        $mor = $kull['mor'];
        $html .= '<div class="npk-mor">';
        $html .= '<h4>' . $mor['navn'] . ' (' . $mor['registreringsnummer'] . ')</h4>';
        
        // Badge display
        if ($mor['elitehund']) {
            $html .= '<span class="badge elite">ELITEHUND</span>';
        }
        if ($mor['avlshund']) {
            $html .= '<span class="badge avl">AVLSHUND</span>';
        }
        
        $html .= '</div>';
        
        // Far med badges
        $far = $kull['far'];
        $html .= '<div class="npk-far">';
        $html .= '<h4>' . $far['navn'] . ' (' . $far['registreringsnummer'] . ')</h4>';
        
        if ($far['elitehund']) {
            $html .= '<span class="badge elite">ELITEHUND</span>';
        }
        if ($far['avlshund']) {
            $html .= '<span class="badge avl">AVLSHUND</span>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

// WordPress shortcode for live display - INGEN CACHING
function npk_valpeliste_shortcode($atts) {
    // Hent fresh data direkte fra API hver gang
    $data = npk_get_live_data();
    
    if (isset($data['error'])) {
        return '<div class="npk-error">Feil: ' . $data['error'] . '</div>';
    }
    
    return npk_display_valpeliste_from_data($data);
}

function npk_display_valpeliste_from_data($data) {
    $html = '<div class="npk-valpeliste">';
    $html .= '<div class="npk-metadata">';
    $html .= '<p>Oppdatert: ' . date('d.m.Y H:i', strtotime($data['metadata']['ekstraksjonstidspunkt'])) . '</p>';
    $html .= '<p>Antall kull: ' . $data['metadata']['antall_kull'] . '</p>';
    $html .= '</div>';
    
    foreach ($data['kull'] as $kull) {
        $html .= '<div class="npk-kull-card">';
        
        // Kull info
        $html .= '<div class="kull-header">';
        $html .= '<h3>Kull ' . $kull['kull_info']['KUID'] . '</h3>';
        $html .= '<span class="kennel">' . $kull['oppdretter']['kennel'] . '</span>';
        $html .= '</div>';
        
        // Foreldre
        $html .= '<div class="parents">';
        
        // Mor
        $mor = $kull['mor'];
        $html .= '<div class="parent mother">';
        $html .= '<h4>Mor: ' . $mor['navn'] . '</h4>';
        $html .= '<p class="regnr">' . $mor['registreringsnummer'] . '</p>';
        
        $badges = [];
        if ($mor['elitehund']) $badges[] = '<span class="badge elite">ELITEHUND</span>';
        if ($mor['avlshund']) $badges[] = '<span class="badge avl">AVLSHUND</span>';
        
        if (!empty($badges)) {
            $html .= '<div class="badges">' . implode(' ', $badges) . '</div>';
        } else {
            $html .= '<div class="no-badges">Ingen spesielle merknader</div>';
        }
        
        $html .= '</div>';
        
        // Far
        $far = $kull['far'];
        $html .= '<div class="parent father">';
        $html .= '<h4>Far: ' . $far['navn'] . '</h4>';
        $html .= '<p class="regnr">' . $far['registreringsnummer'] . '</p>';
        
        $badges = [];
        if ($far['elitehund']) $badges[] = '<span class="badge elite">ELITEHUND</span>';
        if ($far['avlshund']) $badges[] = '<span class="badge avl">AVLSHUND</span>';
        
        if (!empty($badges)) {
            $html .= '<div class="badges">' . implode(' ', $badges) . '</div>';
        } else {
            $html .= '<div class="no-badges">Ingen spesielle merknader</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>'; // parents
        
        // Annonse tekst
        if (!empty($kull['annonse_tekst'])) {
            $html .= '<div class="annonse">';
            $html .= '<h5>Annonse:</h5>';
            $html .= '<p>' . wp_kses_post($kull['annonse_tekst']) . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // kull-card
    }
    
    $html .= '</div>'; // npk-valpeliste
    
    return $html;
}

// Registrer shortcode
add_shortcode('npk_valpeliste', 'npk_valpeliste_shortcode');

// AJAX endpoint for manual refresh - INGEN CACHING
add_action('wp_ajax_npk_refresh_data', 'npk_ajax_refresh_data');
add_action('wp_ajax_nopriv_npk_refresh_data', 'npk_ajax_refresh_data');

function npk_ajax_refresh_data() {
    // Hent fresh data direkte
    $data = npk_get_live_data();
    
    if (isset($data['error'])) {
        wp_send_json_error($data['error']);
    } else {
        wp_send_json_success(['message' => 'Data hentet fresh', 'count' => $data['metadata']['antall_kull']]);
    }
}

?>
