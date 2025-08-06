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

/**
 * Display function for valpeliste from live data
 * Note: Main shortcode function is defined in npk_valpeliste.php
 */
function npk_display_valpeliste_from_data($data) {
    // Start container med samme struktur som den gamle
    $html = '<div class="valpeliste-container"><div class="valpeliste-card-container">';
    
    // Metadata header
    $html .= '<div class="npk-metadata">';
    $html .= '<p>Oppdatert: ' . date('d.m.Y H:i', strtotime($data['metadata']['ekstraksjonstidspunkt'])) . '</p>';
    $html .= '<p>Antall kull: ' . $data['metadata']['antall_kull'] . '</p>';
    $html .= '</div>';
    
    // NYTT: Splitt kull i godkjente og andre
    $godkjente_kull = [];
    $andre_kull = [];
    
    foreach ($data['kull'] as $kull) {
        $is_godkjent = isset($kull['kull_info']['godkjent_avlskriterier']) && $kull['kull_info']['godkjent_avlskriterier'];
        if ($is_godkjent) {
            $godkjente_kull[] = $kull;
        } else {
            $andre_kull[] = $kull;
        }
    }
    
    // Sorter hver gruppe alfabetisk på kennel
    usort($godkjente_kull, function($a, $b) {
        return strcmp($a['oppdretter']['kennel'] ?? '', $b['oppdretter']['kennel'] ?? '');
    });
    
    usort($andre_kull, function($a, $b) {
        return strcmp($a['oppdretter']['kennel'] ?? '', $b['oppdretter']['kennel'] ?? '');
    });
    
    // FØRSTE SEKSJON: Godkjente parringer
    if (!empty($godkjente_kull)) {
        $html .= '<h2 class="valpeliste-section-title approved">✅ Godkjente parringer</h2>';
        $html .= '<div class="valpeliste-card-group">';
        
        foreach ($godkjente_kull as $kull) {
            $html .= npk_render_kull_card($kull, true);
        }
        
        $html .= '</div>'; // End godkjente group
    }
    
    // ANDRE SEKSJON: Andre parringer
    if (!empty($andre_kull)) {
        $html .= '<h2 class="valpeliste-section-title other">📋 Andre parringer</h2>';
        $html .= '<div class="valpeliste-card-group">';
        
        foreach ($andre_kull as $kull) {
            $html .= npk_render_kull_card($kull, false);
        }
        
        $html .= '</div>'; // End andre group
    }
    
    $html .= '</div></div>'; // End containers
    
    return $html;
}

/**
 * Hjelpe-funksjon for å rendere et enkelt kull-kort
 */
function npk_render_kull_card($kull, $is_godkjent = false) {
    $card_class = $is_godkjent ? 'valpeliste-card approved' : 'valpeliste-card';
    
    $html = '<div class="' . $card_class . '">';
    
    // Card top section
    $html .= '<div class="valpeliste-card-top">';
    
    // Header info
    $html .= '<div class="valpeliste-card-header">';
    $html .= '<h3>' . esc_html($kull['oppdretter']['kennel']) . '</h3>';
    $html .= '<span class="valpeliste-date">Forventet: ' . esc_html($kull['kull_info']['fodt']) . '</span>';
    
    // Godkjenningsbadge - kun for godkjente kull
    if ($is_godkjent) {
        $html .= '<span class="valpeliste-badge godkjent-avl">✅ Godkjent iht. avlskriterier</span>';
    }
    $html .= '</div>';
    
    // Contact info
    $html .= '<div class="valpeliste-info">';
    $html .= '<div class="valpeliste-info-inner">';
    $html .= '<div class="valpeliste-info-row"><span class="valpeliste-label">Oppdretter:</span> ' . esc_html($kull['oppdretter']['navn']) . '</div>';
    $html .= '<div class="valpeliste-info-row"><span class="valpeliste-label">Sted:</span> ' . esc_html($kull['oppdretter']['sted'] ?? 'Ikke oppgitt') . '</div>';
    if (!empty($kull['oppdretter']['kontakt']['telefon'])) {
        $html .= '<div class="valpeliste-info-row"><span class="valpeliste-label">Telefon:</span> ' . esc_html($kull['oppdretter']['kontakt']['telefon']) . '</div>';
    }
    if (!empty($kull['oppdretter']['kontakt']['epost'])) {
        $html .= '<div class="valpeliste-info-row"><span class="valpeliste-label">E-post:</span> ' . esc_html($kull['oppdretter']['kontakt']['epost']) . '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Card body content
    $html .= '<div class="valpeliste-card-body">';
    
    // Parents side by side
    $html .= '<div class="valpeliste-parents-grid">';
    
    // Far column
    $html .= '<div class="valpeliste-parent-column">';
    $html .= '<div class="valpeliste-parent-row">';
    $html .= '<span class="valpeliste-label">Far:</span> ';
    $html .= '<span class="valpeliste-parent-info">';
    $html .= '<span class="valpeliste-value">' . esc_html($kull['far']['navn']);
    if (!empty($kull['far']['registreringsnummer'])) {
        $html .= ' (' . esc_html($kull['far']['registreringsnummer']) . ')';
    }
    $html .= '</span>';
    
    // Father badges
    $father_badges = '';
    if (isset($kull['far']['elitehund']) && $kull['far']['elitehund']) {
        $father_badges .= '<span class="valpeliste-badge elitehund">Elitehund</span>';
    }
    if (isset($kull['far']['avlshund']) && $kull['far']['avlshund']) {
        $father_badges .= '<span class="valpeliste-badge avlshund">Avlshund</span>';
    }
    if (!empty($father_badges)) {
        $html .= ' ' . $father_badges;
    }
    $html .= '</span>';
    $html .= '</div>';
    
    // Father details
    if (isset($kull['far']['detaljer']) && !empty($kull['far']['detaljer'])) {
        $html .= '<ul class="valpeliste-parent-details">';
        foreach ($kull['far']['detaljer'] as $key => $value) {
            if (!empty($value)) {
                $html .= '<li><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</li>';
            }
        }
        $html .= '</ul>';
    }
    $html .= '</div>'; // End far column
    
    // Mor column
    $html .= '<div class="valpeliste-parent-column">';
    $html .= '<div class="valpeliste-parent-row">';
    $html .= '<span class="valpeliste-label">Mor:</span> ';
    $html .= '<span class="valpeliste-parent-info">';
    $html .= '<span class="valpeliste-value">' . esc_html($kull['mor']['navn']);
    if (!empty($kull['mor']['registreringsnummer'])) {
        $html .= ' (' . esc_html($kull['mor']['registreringsnummer']) . ')';
    }
    $html .= '</span>';
    
    // Mother badges
    $mother_badges = '';
    if (isset($kull['mor']['elitehund']) && $kull['mor']['elitehund']) {
        $mother_badges .= '<span class="valpeliste-badge elitehund">Elitehund</span>';
    }
    if (isset($kull['mor']['avlshund']) && $kull['mor']['avlshund']) {
        $mother_badges .= '<span class="valpeliste-badge avlshund">Avlshund</span>';
    }
    if (!empty($mother_badges)) {
        $html .= ' ' . $mother_badges;
    }
    $html .= '</span>';
    $html .= '</div>';
    
    // Mother details
    if (isset($kull['mor']['detaljer']) && !empty($kull['mor']['detaljer'])) {
        $html .= '<ul class="valpeliste-parent-details">';
        foreach ($kull['mor']['detaljer'] as $key => $value) {
            if (!empty($value)) {
                $html .= '<li><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</li>';
            }
        }
        $html .= '</ul>';
    }
    $html .= '</div>'; // End mor column
    
    $html .= '</div>'; // End parents grid
    
    // NYTT: Vis alle hunder i kullet med deres badges (som i bildet)
    if (isset($kull['hunder']) && !empty($kull['hunder'])) {
        $html .= '<div class="valpeliste-dogs-section">';
        $html .= '<h4>Hunder i kullet:</h4>';
        $html .= '<div class="valpeliste-dogs-list">';
        
        foreach ($kull['hunder'] as $hund) {
            $html .= '<div class="valpeliste-dog-item">';
            $html .= '<span class="valpeliste-dog-name">' . esc_html($hund['navn'] ?? 'Ukjent');
            if (!empty($hund['registreringsnummer'])) {
                $html .= ' (' . esc_html($hund['registreringsnummer']) . ')';
            }
            $html .= '</span>';
            
            // Vis badges for hver hund
            $dog_badges = '';
            if (isset($hund['elitehund']) && $hund['elitehund']) {
                $dog_badges .= '<span class="valpeliste-badge elitehund">Elitehund</span>';
            }
            if (isset($hund['avlshund']) && $hund['avlshund']) {
                $dog_badges .= '<span class="valpeliste-badge avlshund">Avlshund</span>';
            }
            if (!empty($dog_badges)) {
                $html .= ' ' . $dog_badges;
            }
            
            // Vis viktige detaljer (HD, Jaktindeks, etc.)
            if (isset($hund['detaljer']) && !empty($hund['detaljer'])) {
                $html .= '<div class="valpeliste-dog-details">';
                
                // Prioriterte felter i bestemt rekkefølge
                $priority_fields = [
                    'HD' => 'HD',
                    'Jaktindeks' => 'Jaktindeks', 
                    'Alternativ HD' => 'Alt. HD',
                    'Alternativ HD (mor)' => 'Alt. HD (mor)',
                    'Stående jaktindeks' => 'Stående jakt.',
                    'Avlshund' => 'Avlshund',
                    'Elitehund' => 'Elitehund'
                ];
                
                foreach ($priority_fields as $key => $display_name) {
                    if (!empty($hund['detaljer'][$key])) {
                        $html .= '<span class="valpeliste-detail-item"><strong>' . esc_html($display_name) . ':</strong> ' . esc_html($hund['detaljer'][$key]) . '</span>';
                    }
                }
                
                // Vis andre detaljer som ikke er i prioriterte liste
                foreach ($hund['detaljer'] as $key => $value) {
                    if (!empty($value) && !array_key_exists($key, $priority_fields)) {
                        $html .= '<span class="valpeliste-detail-item"><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</span>';
                    }
                }
                $html .= '</div>';
            }
            
            $html .= '</div>'; // End dog item
        }
        
        $html .= '</div>'; // End dogs list
        $html .= '</div>'; // End dogs section
    }
    
    // Annonsetekst (som i den gamle)
    if (!empty($kull['annonse_tekst'])) {
        $html .= '<div class="valpeliste-announcement">';
        $html .= '<h4>Annonse:</h4>';
        $html .= '<p>' . wp_kses_post($kull['annonse_tekst']) . '</p>';
        $html .= '</div>';
    }
    
    $html .= '</div>'; // End card body
    $html .= '</div>'; // End card
    
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
